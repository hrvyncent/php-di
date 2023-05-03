<?php
declare(strict_types=1);

namespace Xeno;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Xeno\Exceptions\BindingsNotFoundException;
use Xeno\Exceptions\ContainerException;

final class Container implements ContainerInterface
{
    /**
     * The array containing all the application class bindings.
     */
    private array $bindings = [];    

    /**
     * Get the entry in the bindings.
     * 
     * @throws \Xeno\Exceptions\BindingsNotFoundException
     * @throws \Xeno\Exceptions\ContainerException
     */
    public function get(string $abstract) : mixed
    {
        if (!$this->has($abstract)) {
            throw new BindingsNotFoundException(
                sprintf("The [%s] do not exists in the bindings.", $abstract)
            );
        }

        try {
            $binding = $this->bindings[$abstract];            
            $concrete = $this->make($binding);
        } catch (ContainerException $ex) {
            
            //Throw the exception if there is one
            throw $ex;
        }

        return $concrete;
    }

    /**
     * Check if the abstract exists in the bindings.
     */
    public function has(string $abstract): bool
    {
        return (bool) isset ($this->bindings[$abstract]);
    }

    /**
     * Resolve the concrete implementation of the given abstract.
     * 
     * @throws \Micra\Core\Exceptions\ContainerException
     */
    public function make(string $concrete) : mixed
    {
        // Get the concrete class information using Reflection Class
        $reflection = $this->getReflection($concrete);

        /**
         * If the concrete class is not instantiable, throw an exception.
         */
        if (! $reflection->isInstantiable()) {
            throw new ContainerException(
                sprintf("The [%s] cannot be resolve because its not instantiable", $reflection->getName())
            );
        }

        $constructor = $reflection->getConstructor();

        /**
         * Return the instance of the concrete if there's no constructor found.
         */
        if (is_null($constructor)) {
            return $reflection->newInstance();
        }

        /**
         * If the number of parameters is equals to 0 we will
         * return its instance instead of continuing.
        */
        if ($constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $dependencies = $this->getDependencies($constructor->getParameters());

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Bind the concrete implementation to abstract
     */
    public function bind(string $abstract, string $concrete = null) : void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Get the class information using `ReflectionClass`
     */
    private function getReflection(string $concrete) : ReflectionClass
    {
        return new ReflectionClass($concrete);
    }

    /**
     * Get the resolve dependencies for the concrete class.
     */
    private function getDependencies(array $parameters) : array
    {
        return array_map(
            callback: function (ReflectionParameter $param) {
                $name = $param->getName();
                $type = $param->getType();

                if (is_null($type)) {
                    throw new ContainerException(
                        sprintf("The [%s] does not have a type hint so it can't be resolve", $name)
                    );
                }

                if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
                    throw new ContainerException(
                        sprintf(
                            "The [%s] is failed to resolve. The [%s] cannot be an instance of %s or %s", 
                            $name, $name, ReflectionUnionType::class,ReflectionIntersectionType::class
                        )
                    );
                }

                if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                    throw new ContainerException(
                        sprintf("The [%s] is failed to resolve. The [%s] cannot be a built-in type.", $name)
                    );
                }

                return $this->get($type->getName());
            },
            array: $parameters,
        );
    }
}