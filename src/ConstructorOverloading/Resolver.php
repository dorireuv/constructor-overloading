<?php

namespace ConstructorOverloading;

use ConstructorOverloading\ArgType\ArgumentTypeInterface;
use ConstructorOverloading\ArgType\ArrayArgumentType;
use ConstructorOverloading\ArgType\BooleanArgumentType;
use ConstructorOverloading\ArgType\DoubleArgumentType;
use ConstructorOverloading\ArgType\IntegerArgumentType;
use ConstructorOverloading\ArgType\NullArgumentType;
use ConstructorOverloading\ArgType\NullObjectArgumentType;
use ConstructorOverloading\ArgType\ObjectArgumentType;
use ConstructorOverloading\ArgType\ResourceArgumentType;
use ConstructorOverloading\ArgType\StringArgumentType;
use ConstructorOverloading\ArgType\UnknownArgumentType;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

/**
 * Resolver - resolves which constructor should be called
 *
 * How does it work?
 * Parameter type is determined using the following:logic
 * If it has a type hint use it, otherwise
 * If it has a default value use it, otherwise
 * If it has doc hint (@param [type] [param_name]) use it, otherwise
 * any type will suffice
 *
 * Best usage:
 * 1. For objects use type hint: function (A $a)
 * 2. For primitives use PHP doc: @param int $int
 *
 */
class Resolver
{

    /**
     * Gets argument type for a concrete argument
     *
     * @param mixed $arg argument
     *
     * @return ArgumentTypeInterface argument type
     */
    private function getArgTypeByConcreteArg($arg)
    {
        // get-type might return:
        // "boolean"
        // "integer"
        // "double"
        // "string"
        // "array"
        // "object"
        // "resource"
        // "NULL"
        // "unknown type"

        $type = gettype($arg);
        switch ($type) {
            case 'boolean':
                $argType = new BooleanArgumentType();
                break;

            case 'integer':
                $argType = new IntegerArgumentType();
                break;

            case 'double':
                $argType = new DoubleArgumentType();
                break;

            case 'string':
                $argType = new StringArgumentType();
                break;

            case 'array':
                $argType = new ArrayArgumentType();
                break;

            case 'object':
                $argType = new ObjectArgumentType(get_class($arg));
                break;

            case 'NULL':
                $argType = new NullArgumentType();
                break;

            case 'resource':
                $argType = new ResourceArgumentType();
                break;

            case 'unknown type':
            default:
                $argType = new UnknownArgumentType();
        }

        return $argType;
    }

    /**
     * Gets the arg type of a give doc type
     *
     * @param string $docType doc type
     *
     * @return ArgumentTypeInterface argument type
     */
    private function getArgTypeByDocType($docType)
    {
        $canonicalDocType = strtolower($docType);
        $docTypeToArgTypeMapping = array(
            'bool'     => new BooleanArgumentType(),
            'boolean'  => new BooleanArgumentType(),
            'int'      => new IntegerArgumentType(),
            'integer'  => new IntegerArgumentType(),
            'double'   => new DoubleArgumentType(),
            'float'    => new DoubleArgumentType(),
            'string'   => new StringArgumentType(),
            'str'      => new StringArgumentType(),
            'arr'      => new ArrayArgumentType(),
            'array'    => new ArrayArgumentType(),
            'resource' => new ResourceArgumentType(),
        );
        if (isset($docTypeToArgTypeMapping[$canonicalDocType])) {
            return $docTypeToArgTypeMapping[$canonicalDocType];
        }

        return new ObjectArgumentType($docType);
    }

    /**
     * Gets argument type for reflection parameter
     *
     * @param ReflectionParameter $reflectionParameter reflection parameter
     *
     * @return ArgumentTypeInterface[] argument types
     */
    private function getArgTypesByReflectionParameter(ReflectionParameter $reflectionParameter)
    {
        if ($reflectionParameter->isArray()) {
            $argTypes = array(new ArrayArgumentType());
        } else if ($reflectionParameter->getClass() !== null) {
            $className = $reflectionParameter->getClass()->getName();
            $argTypes = array(new ObjectArgumentType($className));
            if ($reflectionParameter->allowsNull()) {
                $argTypes[] = new NullObjectArgumentType($className);
            }
        } else {
            $argTypes = array();

            // try to figure out by using the default value
            if ($reflectionParameter->isDefaultValueAvailable()) {
                $defaultValue = $reflectionParameter->getDefaultValue();
                $argTypes[] = $this->getArgTypeByConcreteArg($defaultValue);
            } else {
                // try to use method documentation
                $comment = $reflectionParameter->getDeclaringFunction()->getDocComment();
                if ($comment !== false && $comment !== '') {
                    $lines = explode("\n", str_replace("\r\n", "\n", $comment));
                    $parameterName =  $reflectionParameter->getName();
                    $pattern = sprintf('/^@param[\\s]+([a-z\\\\]+(?:\\|[a-z\\\\]+)*)[\\s]+\\$%s/i', $parameterName);
                    foreach ($lines as $line) {
                        $line = trim($line, ' /*');
                        if (preg_match($pattern, $line, $matches)) {
                            $docTypes = explode('|', $matches[1]);
                            foreach ($docTypes as $docType) {
                                $argType = $this->getArgTypeByDocType($docType);
                                $argTypes[] = $argType;
                            }

                            // break the loop because the line was found
                            break;
                        }
                    }
                } else {
                    $argTypes[] = new UnknownArgumentType();
                }
            }
        }

        return $argTypes;
    }

    /**
     * Resolves the best constructor name for the given arguments
     *
     * @param string $className class name
     * @param array $args arguments
     *
     * @return string the constructor name
     *
     * @throws RuntimeException when no constructor found
     */
    public function resolve($className, array $args)
    {
        $reflectionClass = new ReflectionClass($className);
        $reflectionMethods = $reflectionClass->getMethods(
            ReflectionMethod::IS_PRIVATE
            | ReflectionMethod::IS_PROTECTED
        );

        $argTypes = array();
        foreach ($args as $arg) {
            $argTypes[] = $this->getArgTypeByConcreteArg($arg);
        }

        $numOfArgs = count($args);
        $candidate = null;
        $candidateDistance = 0;
        $candidateOptionalParamsDiff = 0;
        foreach ($reflectionMethods as $reflectionMethod) {
            // validate function name
            $name = $reflectionMethod->getName();
            if (strpos($name, '_construct') !== 0) {
                // skip non constructor methods
                continue;
            }

            // validate constructor number of arguments
            $numOfConstructorArgs = $reflectionMethod->getNumberOfParameters();
            if ($numOfConstructorArgs < $numOfArgs) {
                // skip methods with not enough arguments
                continue;
            }

            // if the number of the constructor arguments is greater than the given
            // arguments make sure they are all optional
            $constructorParameters = $reflectionMethod->getParameters();
            if ($numOfConstructorArgs > $numOfArgs) {
                $shouldSkip = false;
                for ($i = $numOfArgs; $i < $numOfConstructorArgs; $i++) {
                    $reflectionParameter = $constructorParameters[$i];
                    if (!$reflectionParameter->isOptional()) {
                        $shouldSkip = true;
                        break;
                    }
                }
                if ($shouldSkip) {
                    continue;
                }
            }
            $optionalParamsDiff = $numOfConstructorArgs - $numOfArgs;

            // compare arguments types
            $distance = 0;
            $shouldSkip = false;
            for ($i = 0; $i < $numOfArgs; $i++) {
                $argType = $argTypes[$i];
                $constructorParameter = $constructorParameters[$i];
                $constructorParameterArgTypes = $this->getArgTypesByReflectionParameter($constructorParameter);
                $minArgTypeDistance = -1;
                foreach ($constructorParameterArgTypes as $constructorParameterArgType) {
                    $argTypeDistance = $constructorParameterArgType->getDistanceOf($argType);
                    if (
                        $argTypeDistance >= 0
                        && ($minArgTypeDistance === -1 || $argTypeDistance < $minArgTypeDistance)
                    ) {
                        $minArgTypeDistance = $argTypeDistance;
                    }
                }
                if ($minArgTypeDistance < 0) {
                    $shouldSkip = true;
                    break;
                }

                $distance += $minArgTypeDistance;
            }
            if ($shouldSkip) {
                continue;
            }

            // found a candidate
            if (
                !isset($candidate)
                || $distance < $candidateDistance
                || ($distance === $candidateDistance && $optionalParamsDiff < $candidateOptionalParamsDiff)
            ) {
                $candidate = $reflectionMethod;
                $candidateDistance = $distance;
                $candidateOptionalParamsDiff = $optionalParamsDiff;
            }

            if ($candidate && $candidateDistance === 0 && $candidateOptionalParamsDiff === 0) {
                // found the best candidate
                break;
            }
        }
        if ($candidate) {
            return $candidate->getName();
        }

        throw new RuntimeException(
            sprintf(
                "failed to find constructor for %s with arguments %s",
                $reflectionClass->getName(),
                print_r($argTypes, true)
            )
        );
    }
}
