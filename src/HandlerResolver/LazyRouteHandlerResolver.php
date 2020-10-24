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
use InvalidArgumentException;

class LazyRouteHandlerResolver implements RouteHandlerResolverInterface
{
    private RouteHandlerResolverInterface $resolver;

    public function __construct(RouteHandlerResolverInterface $resolver)
    {
        if ($resolver instanceof self) {
            throw new InvalidArgumentException('Cannot use self as route resolver');
        }

        $this->resolver = $resolver;
    }

    public function resolve($callable): Closure
    {
        $promise = new RouteHandlerPromise(function () use ($callable) {
            return $this->resolver->resolve($callable);
        });

        return Closure::fromCallable($promise);
    }
}
