<?php

namespace Xeno;

use Psr\Container\ContainerInterface;
use Xeno\Exceptions\BindingsNotFoundException;
use Xeno\Exceptions\ContainerException;

class Container implements ContainerInterface
{
    /**
     * The container bindings.
     */
    private array $bindings = [];

    /**
     * Get the concrete implementation of the given abstract.
     * 
     * @throws \Xeno\Exceptions\BindingsNotFoundException
     * @throws \Xeno\Exceptions\ContainerException
     */
    public function get(string $abstract) : mixed
    {
        if (! $this->has($abstract)) {
            throw new BindingsNotFoundException(
                message: sprintf('The [%s] does the found in the container bindings.', $abstract)
            );
        }

        try {
            $concrete = $this->resolve($abstract);
        } catch (ContainerException $ex) {
            throw new ContainerException(
                message: sprintf('The concrete implementation of [%s] is cannot be resolve.', $abstract),
            );
        }

        return $concrete;
    }

    /**
     * Check if the abstract exists in the bindings.
     */
    public function has(string $abstract): bool
    {
        return (bool) isset($this->bindings[$abstract]);
    }

    /**
     * Bind the concrete to the abstract.
     */
    public function bind(string $abstract, mixed $concrete = null) : void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    public function resolve()
    {}
}