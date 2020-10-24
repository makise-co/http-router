<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Tests;

use DI\Container;
use MakiseCo\Http\Router\Exception\WrongRouteHandlerException;
use MakiseCo\Http\Router\RouteCollector;
use MakiseCo\Http\Router\RouteCollectorLazyFactory;

class RouteCollectorLazyTest extends RouteCollectorTest
{
    protected function createRouteCollector(): RouteCollector
    {
        return (new RouteCollectorLazyFactory())->create(
            new Container(),
        );
    }

    public function testRouteHandlerResolvedOnCompileStage(): void
    {
        $this->collector->get('/', $this->response);
        [$route] = $this->collector->getRoutes();

        self::assertNotSame($this->response, $route->getHandler());

        $this->collector->getRouter();

        self::assertSame($this->response, $route->getHandler());
    }

    public function testWrongRouteHandler(): void
    {
        $this->collector->get('/', static function () {});

        $this->expectException(WrongRouteHandlerException::class);

        $this->collector->getRouter();
    }
}
