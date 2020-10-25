<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Invoker;

use Invoker\InvokerInterface;
use MakiseCo\Http\Router\RouteInterface;
use MakiseCo\Http\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpDiRouteInvoker implements RouteInvokerInterface
{
    private InvokerInterface $invoker;

    /**
     * @var array<class-string>
     */
    private array $requestAliases;

    /**
     * PhpDiRouteInvoker constructor.
     * @param InvokerInterface $invoker
     * @param array<class-string> $requestAliases
     */
    public function __construct(InvokerInterface $invoker, array $requestAliases = [])
    {
        $this->invoker = $invoker;
        $this->requestAliases = $requestAliases;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute(RouteInterface::class);
        $args = $request->getAttribute(Router::ROUTE_ARGS, []);

        $handler = $route->getHandler();

        // bind request
        $args[ServerRequestInterface::class] = $request;

        // bind request to user supplied aliases
        foreach ($this->requestAliases as $requestAlias) {
            $args[$requestAlias] = $request;
        }

        return $this->invoker->call($handler, $args);
    }
}
