<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use FastRoute\Dispatcher\GroupCountBased;

class RouterFactory implements RouterFactoryInterface
{
    public function create(RouteCollectorInterface $collector): Router
    {
        return new Router(
            new GroupCountBased($collector->getData())
        );
    }
}
