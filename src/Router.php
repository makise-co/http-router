<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    public const ROUTE_ARGS = 'args';

    private Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getRequestTarget()
        );

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new Exception\RouteNotFoundException();
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new Exception\MethodNotAllowedException($routeInfo[1]);
            case \FastRoute\Dispatcher::FOUND:
                /* @var RouteInterface $route */
                [, $route, $routeArgs] = $routeInfo;

                $request = $request
                    ->withAttribute(RouteInterface::class, $route)
                    ->withAttribute(self::ROUTE_ARGS, $routeArgs);

                // transfer route attributes to the request attributes
//                foreach ($route->getAttributes() as $key => $value) {
//                    $request->attributes->set($key, $value);
//                }

                return $route->getPipe()->handle($request);
        }

        throw new Exception\RouteNotFoundException();
    }
}
