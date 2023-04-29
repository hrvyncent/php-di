<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Xeno\Container;

class ContainerTest extends TestCase
{
    private Container $container;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container;
    }

    public function test_it_will_return_false_when_the_id_does_not_found()
    {
        $result = $this->container->has(Dummy::class);

        $this->assertFalse($result);
    }

    public function test_it_will_return_true_if_the_id_exists_in_the_bindings()
    {
        $this->container->bind(Sample::class);

        $result  = $this->container->has(Sample::class);

        $this->assertTrue($result);
    }

    public function test_it_will_bind_the_concrete_implementation_in_the_container()
    {
        $this->container->bind(Abstracts::class, Concrete::class);

        $result = $this->container->has(Abstracts::class);
        
        $this->assertTrue($result);
    }
}   