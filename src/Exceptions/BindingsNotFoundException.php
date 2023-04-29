<?php

namespace Xeno\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class BindingsNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}