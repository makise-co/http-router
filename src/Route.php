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

class Route implements RouteInterface
{
    private string $name = '';

    /**
     * @var string[]
     */
    private array $methods;

    private string $path;

    private Closure $handler;

    /**
     * @var MiddlewareInterface[]|string[]
     */
    private array $middlewares = [];

    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    private ?MiddlewarePipe $pipe = null;

    /**
     * Route can't be modified after route is compiled
     *
     * @var bool
     */
    private bool $isCompiled = false;

    /**
     * @param string[] $methods
     * @param string $path
     * @param Closure $handler
     */
    public function __construct(
        array $methods,
        string $path,
        Closure $handler
    ) {
        $this->methods = $methods;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getHandler(): Closure
    {
        return $this->handler;
    }

    public function getPipe(): MiddlewarePipe
    {
        if ($this->pipe === null) {
            throw new \RuntimeException('Route doesn\'t have pipe');
        }

        return $this->pipe;
    }

    public function setName(string $name): self
    {
        $this->checkIsCompiled();

        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withMiddleware($middleware): self
    {
        $this->checkIsCompiled();

        $this->middlewares[] = $middleware;

        return $this;
    }

    public function withAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function compile(array $args): void
    {
        if ($this->isCompiled) {
            return;
        }

        $this->isCompiled = true;

        $handler = $args['handler'] ?? null;
        // support of lazy handler resolution
        if ($handler instanceof Closure) {
            $this->handler = $handler;
        }

        $pipe = $args['pipe'] ?? null;
        if ($pipe instanceof MiddlewarePipe) {
            $this->pipe = $pipe;
        }
    }

    public function isCompiled(): bool
    {
        return $this->isCompiled;
    }

    private function checkIsCompiled(): void
    {
        if ($this->isCompiled) {
            throw new \BadMethodCallException('Route is compiled');
        }
    }
}
