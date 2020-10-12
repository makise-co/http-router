<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\HandlerResolver;

use Closure;

interface RouteHandlerResolverInterface
{
    /**
     * @param string|string[]|callable|Closure $callable
     *
     * @return Closure
     */
    public function resolve($callable): Closure;
}
