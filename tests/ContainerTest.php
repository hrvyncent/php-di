<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Xeno\Exceptions\BindingsNotFoundException;
use Xeno\Exceptions\ContainerException;
use Xeno\Container;

final class ContainerTest extends TestCase
{
    private Container $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Container;
    }

    public function test_it_will_return_false_if_the_bindings_do_not_exists()
    {
        $result = $this->app->has(Dummy::class);
    
        $this->assertFalse($result);
    }

    public function test_it_will_return_the_concrete_name_if_the_abstract_exsist_it_the_bindings()
    {
        $this->app->bind(DummyInterface::class, Dummy::class);
        $this->app->bind(DummyDependencyInterface::class, DummyDependency::class);

        $result = $this->app->get(DummyInterface::class);

        $this->assertInstanceOf(Dummy::class, $result);
    }

    public function test_it_will_throw_a_binding_not_found_exception_if_the_bindings_not_found()
    {
        $this->expectException(BindingsNotFoundException::class);

        $this->app->get(DummyInterface::class);
    }

    public function test_it_will_throw_an_exception_if_union_type()
    {
        $this->expectException(ContainerException::class);

        $this->app->bind(DummyInterface::class, DummyWithUnionType::class);

        $this->app->get(DummyInterface::class);
    }

    public function test_it_will_throw_an_exception_if_builtin_type()
    {
        $this->expectException(ContainerException::class);

        $this->app->bind(DummyInterface::class, DummyWithBuiltInType::class);

        $this->app->get(DummyInterface::class);
    }

    public function test_it_will_throw_an_exception_if_intersection_type()
    {
        $this->expectException(ContainerException::class);

        $this->app->bind(DummyInterface::class, DummyWithIntersectionType::class);

        $this->app->get(DummyInterface::class);
    }
}

interface DummyInterface {}
interface DummyDependencyInterface {}

class DummyDependency implements DummyDependencyInterface {}
class Dummy implements DummyInterface
{
    public function __construct(private DummyDependencyInterface $class) {}
}

class DummyWithUnionType implements DummyInterface
{
    public function __construct(private DummyInterface|DummyDependencyInterface $class) {}
}

class DummyWithBuiltInType implements DummyInterface
{
    public function __construct(private DummyInterface|DummyDependencyInterface $class) {}
}

class DummyWithIntersectionType implements DummyInterface
{
    public function __construct(private DummyInterface&DummyDependencyInterface $class) {}
}
