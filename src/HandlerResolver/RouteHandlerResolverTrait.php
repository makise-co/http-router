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
use MakiseCo\Http\Router\Exception;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;

trait RouteHandlerResolverTrait
{
    /**
     * @param Closure $normalizedHandler
     * @param Closure|callable|string|string[] $rawHandler
     * @throws ReflectionException
     */
    protected function validateReturnType(Closure $normalizedHandler, $rawHandler): void
    {
        $reflectionFunction = new ReflectionFunction($normalizedHandler);
        $returnType = $reflectionFunction->getReturnType();
        if (null === $returnType || $returnType->allowsNull()) {
            throw Exception\WrongRouteHandlerException::forWrongReturnType($rawHandler);
        }

        if (!$returnType instanceof ReflectionNamedType) {
            throw Exception\WrongRouteHandlerException::forWrongReturnType($rawHandler);
        }

        /** @var class-string $typeName */
        $typeName = $returnType->getName();
        if ($typeName === ResponseInterface::class) {
            return;
        }

        try {
            $reflectionClass = new ReflectionClass($typeName);
        } catch (ReflectionException $e) {
            throw Exception\WrongRouteHandlerException::forWrongReturnType($rawHandler);
        }

        if (!$reflectionClass->implementsInterface(ResponseInterface::class)) {
            throw Exception\WrongRouteHandlerException::forWrongReturnType($rawHandler);
        }
    }
}
