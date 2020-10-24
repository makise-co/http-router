<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use MakiseCo\Http\Router\Invoker\RouteInvokerInterface;
use MakiseCo\Middleware\MiddlewarePipeFactoryInterface;

class RouteCompiler implements RouteCompilerInterface
{
    private MiddlewarePipeFactoryInterface $pipeFactory;

    private RouteInvokerInterface $routeInvoker;

    public function __construct(MiddlewarePipeFactoryInterface $pipeFactory, RouteInvokerInterface $routeInvoker)
    {
        $this->pipeFactory = $pipeFactory;
        $this->routeInvoker = $routeInvoker;
    }

    public function compile(RouteInterface $route): void
    {
        // do not compile already compiled routes
        if ($route->isCompiled()) {
            return;
        }

        $handler = $route->getHandler();
        $handlerReflection = new \ReflectionFunction($handler);
        $promisedHandler = null;

        // route handler resolution is promised, lets resolve this promise now
        if ($handlerReflection->getClosureThis() instanceof HandlerResolver\RouteHandlerPromise) {
            // re-throw route resolution exception with debug information
            try {
                $promisedHandler = $handler();
            } catch (Exception\WrongRouteHandlerException $e) {
                throw new Exception\WrongRouteHandlerException(
                    \sprintf("%s %s: %s", \implode('|', $route->getMethods()), $route->getPath(), $e->getMessage()),
                    $e->getHandler(),
                    $e
                );
            }
        }

        $middlewares = $route->getMiddlewares();
        $middlewares[] = $this->routeInvoker;

        $pipe = $this->pipeFactory->create($middlewares);

        $route->compile(['pipe' => $pipe, 'handler' => $promisedHandler]);
    }
}
