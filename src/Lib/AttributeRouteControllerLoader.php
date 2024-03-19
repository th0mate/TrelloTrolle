<?php

namespace App\Trellotrolle\Lib;

use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

class AttributeRouteControllerLoader extends AttributeClassLoader
{
    /**
     * Configures the _controller default parameter of a given Route instance.
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot): void
    {
        $route->setDefault('_controller', $class->getName().'::'.$method->getName());
    }

}