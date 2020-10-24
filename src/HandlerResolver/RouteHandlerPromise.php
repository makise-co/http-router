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
    /**
     * @var mixed
     */
    private $handler;

    private Closure $resolve;

    /**
     * @param mixed $handler route handler to be resolved
     * @param Closure $resolve route handler resolve callback
     */
    public function __construct($handler, Closure $resolve)
    {
        $this->handler = $handler;
        $this->resolve = $resolve;
    }

    /**
     * Resolve route handler promise
     *
     * @return Closure resolved route handler
     */
    public function __invoke(): Closure
    {
        return ($this->resolve)($this->handler);
    }

    /**
     * @return mixed
     */
    public function getRouteHandler()
    {
        return $this->handler;
    }
}
