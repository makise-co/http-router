<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use Invoker\CallableResolver;
use MakiseCo\Http\Router\HandlerResolver\LazyRouteHandlerResolver;
use MakiseCo\Http\Router\HandlerResolver\PhpDiRouteHandlerResolver;
use Psr\Container\ContainerInterface;

class RouteCollectorLazyFactory extends RouteCollectorFactory
{
    public function create(ContainerInterface $container): RouteCollector
    {
        return new RouteCollector(
            new \FastRoute\RouteParser\Std(),
            new \FastRoute\DataGenerator\GroupCountBased(),
            new LazyRouteHandlerResolver(
                new PhpDiRouteHandlerResolver(
                    new CallableResolver($container)
                )
            ),
            $this->getCompiler($container),
            new RouterFactory()
        );
    }
}
