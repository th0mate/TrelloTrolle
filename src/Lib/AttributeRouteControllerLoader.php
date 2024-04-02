<?php

namespace App\Trellotrolle\Lib;

use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

class AttributeRouteControllerLoader extends AttributeClassLoader
{
    /**
     * Configures the _controller default parameter of a given Route instance.
     *
     * @param Route $route A route
     * @param \ReflectionClass $class A class reflection
     * @param \ReflectionMethod $method A method reflection
     * @param object $annot The annotation instance
     *
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot): void
    {
        $route->setDefault('_controller', $this->toSnakeCase($class->getShortName()).'::'.$method->getName());
    }

    /**
     * Converts a camel case controller name to a snake case.
     * @param $controllerName string
     * @return string The controller name in snake case
     */

    private function toSnakeCase($controllerName) : string {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $controllerName)), '_');
    }

}