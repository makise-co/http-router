<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use Closure;
use MakiseCo\Middleware\MiddlewarePipe;
use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface
{
    public const METHOD_GET = 'GET';
    public const METHOD_HEAD = 'HEAD';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_PATCH = 'PATCH';

    public function getName(): string;

    public function getPath(): string;

    /**
     * @return string[]
     */
    public function getMethods(): array;

    /**
     * @return MiddlewareInterface[]|string[]
     */
    public function getMiddlewares(): array;

    public function getHandler(): Closure;

    public function getPipe(): MiddlewarePipe;

    public function setName(string $name): self;

    /**
     * @param MiddlewareInterface|string $middleware
     * @return self
     */
    public function withMiddleware($middleware): self;

    /**
     * Compile route
     *
     * @param mixed[] $args
     */
    public function compile(array $args): void;
}
