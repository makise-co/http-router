<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

interface RouteCollectorInterface
{
    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function addRoute($httpMethod, string $path, $handler): RouteInterface;

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string $prefix
     * @param array<string,mixed> $parameters
     * @param callable $callback
     */
    public function addGroup(string $prefix, array $parameters, callable $callback): void;

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function get(string $path, $handler): RouteInterface;

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function head(string $path, $handler): RouteInterface;

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function post(string $path, $handler): RouteInterface;

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function put(string $path, $handler): RouteInterface;

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function delete(string $path, $handler): RouteInterface;

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function options(string $path, $handler): RouteInterface;

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $path string depends on the used route parser.
     *
     * @param string $path
     * @param callable|\Closure|string|string[] $handler
     *
     * @return RouteInterface
     */
    public function patch(string $path, $handler): RouteInterface;

    /**
     * Get routes from collection
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Get data for fast route dispatcher
     *
     * @return mixed[]
     */
    public function getData(): array;

    /**
     * Get router instance with routes from collection
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface;
}
