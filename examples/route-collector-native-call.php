<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

use DI\Container;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use Invoker\CallableResolver;
use MakiseCo\Http\Router\HandlerResolver\PhpDiRouteHandlerResolver;
use MakiseCo\Http\Router\Invoker\RouteInvokerInterface;
use MakiseCo\Http\Router\RouteCollector;
use MakiseCo\Http\Router\RouteCompiler;
use MakiseCo\Http\Router\RouteInterface;
use MakiseCo\Http\Router\Router;
use MakiseCo\Http\Router\RouterFactory;
use MakiseCo\Middleware\MiddlewarePipeFactory;
use MakiseCo\Middleware\MiddlewareResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../vendor/autoload.php';

class NativeRouteInvoker implements RouteInvokerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute(RouteInterface::class);
        $args = $request->getAttribute(Router::ROUTE_ARGS, []);

        $args[ServerRequestInterface::class] = $request;

        $handler = $route->getHandler();

        return call_user_func_array($handler, $args);
    }
}

function getNativeCallRouteCollector(Container $controllerContainer, Container $appContainer): RouteCollector
{
    return new RouteCollector(
        new Std(),
        new GroupCountBased(),
        new PhpDiRouteHandlerResolver(
            new CallableResolver($controllerContainer)
        ),
        new RouteCompiler(
            new MiddlewarePipeFactory(
                new MiddlewareResolver($controllerContainer)
            ),
            new NativeRouteInvoker()
        ),
        new RouterFactory()
    );
}
