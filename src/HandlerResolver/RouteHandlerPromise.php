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

final class RouteHandlerPromise
{
    private Closure $resolve;

    /**
     * @param Closure $resolve route handler resolve callback
     */
    public function __construct(Closure $resolve)
    {
        $this->resolve = $resolve;
    }

    /**
     * Resolve route handler promise
     *
     * @return Closure resolved route handler
     */
    public function __invoke(): Closure
    {
        return ($this->resolve)();
    }
}
