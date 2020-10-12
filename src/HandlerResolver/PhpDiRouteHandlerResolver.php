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
use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use MakiseCo\Http\Router\Exception;
use ReflectionException;

use function explode;
use function is_string;
use function strpos;

class PhpDiRouteHandlerResolver implements RouteHandlerResolverInterface
{
    use RouteHandlerResolverTrait;

    private CallableResolver $resolver;

    public function __construct(CallableResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritDoc
     */
    public function resolve($callable): Closure
    {
        if (is_string($callable) && 0 !== strpos($callable, '@')) {
            $callable = explode('@', $callable, 2);
        }

        try {
            $handler = Closure::fromCallable($this->resolver->resolve($callable));
        } catch (NotCallableException | ReflectionException $e) {
            throw new Exception\WrongRouteHandlerException($e->getMessage(), $callable, $e);
        }

        $this->validateReturnType($handler, $callable);

        return $handler;
    }
}
