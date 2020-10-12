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
        $middlewares = $route->getMiddlewares();
        $middlewares[] = $this->routeInvoker;

        $pipe = $this->pipeFactory->create($middlewares);

        $route->compile(['pipe' => $pipe]);
    }
}
