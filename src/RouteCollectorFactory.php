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
use Invoker\Invoker;
use Invoker\ParameterResolver;
use MakiseCo\Http\Router\HandlerResolver\PhpDiRouteHandlerResolver;
use MakiseCo\Http\Router\Invoker\PhpDiRouteInvoker;
use MakiseCo\Middleware\MiddlewarePipeFactory;
use MakiseCo\Middleware\MiddlewareResolver;
use Psr\Container\ContainerInterface;

class RouteCollectorFactory implements RouteCollectorFactoryInterface
{
    /**
     * User defined request class aliases for DI
     *
     * @var array<class-string>
     */
    private array $requestAliases;

    /**
     * @param array<class-string> $requestAliases
     */
    public function __construct(array $requestAliases = [])
    {
        $this->requestAliases = $requestAliases;
    }

    public function create(ContainerInterface $container): RouteCollector
    {
        return new RouteCollector(
            new \FastRoute\RouteParser\Std(),
            new \FastRoute\DataGenerator\GroupCountBased(),
            new PhpDiRouteHandlerResolver(
                new CallableResolver($container)
            ),
            $this->getCompiler($container),
            new RouterFactory()
        );
    }

    protected function getCompiler(ContainerInterface $container): RouteCompiler
    {
        return new RouteCompiler(
            new MiddlewarePipeFactory(
                new MiddlewareResolver($container)
            ),
            new PhpDiRouteInvoker(
                new Invoker(
                    new ParameterResolver\ResolverChain(
                        [
                            new ParameterResolver\TypeHintResolver(),
                            new ParameterResolver\AssociativeArrayResolver(),
                            new ParameterResolver\NumericArrayResolver(),
                            new ParameterResolver\Container\TypeHintContainerResolver($container),
                            new ParameterResolver\DefaultValueResolver(),
                        ]
                    ),
                    null, // performance optimization
                ),
                $this->requestAliases
            )
        );
    }
}
