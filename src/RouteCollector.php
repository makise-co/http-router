<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;

use function class_exists;
use function is_string;
use function mb_strpos;
use function rtrim;

class RouteCollector implements RouteCollectorInterface
{
    /**
     * @var RouteInterface[]
     */
    protected array $routes = [];

    protected string $currentGroupPrefix = '';

    /**
     * @var array<string,mixed>
     */
    protected array $currentGroupParameters = [];

    protected HandlerResolver\RouteHandlerResolverInterface $handlerResolver;

    protected RouteParser $routeParser;

    protected DataGenerator $dataGenerator;

    protected RouteCompilerInterface $routeCompiler;

    protected RouterFactoryInterface $routerFactory;

    public function __construct(
        RouteParser $routeParser,
        DataGenerator $dataGenerator,
        HandlerResolver\RouteHandlerResolverInterface $handlerResolver,
        RouteCompilerInterface $routeCompiler,
        RouterFactoryInterface $routerFactory
    ) {
        $this->handlerResolver = $handlerResolver;
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
        $this->routeCompiler = $routeCompiler;
        $this->routerFactory = $routerFactory;
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function addRoute($httpMethod, string $path, $handler): RouteInterface
    {
        $httpMethod = (array)$httpMethod;
        $path = $this->normalizedPath($path);

        $path = $this->currentGroupPrefix . $path;
        if ('' === $path) {
            $path = '/';
        }

        // Parse route URL
        $routeDatum = $this->routeParser->parse($path);

        // Create route handler
        if (is_string($handler) && !class_exists($handler)) {
            $handler = ($this->currentGroupParameters['namespace'] ?? '') . $handler;
        }

        $handler = $this->handlerResolver->resolve($handler);

        $route = new Route(
            $httpMethod,
            $path,
            $handler,
        );
        $this->addGroupParametersToRoute($route);

        foreach ($httpMethod as $method) {
            foreach ($routeDatum as $routeData) {
                $this->dataGenerator->addRoute(
                    $method,
                    $routeData,
                    $route
                );
            }
        }

        $this->routes[] = $route;

        return $route;
    }

    /**
     * @inheritDoc
     */
    public function addGroup(string $prefix, array $parameters, callable $callback): void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $previousGroupParameters = $this->currentGroupParameters;

        $prefix = $this->normalizedPath($prefix);

        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $this->currentGroupParameters = Internal\mergeRecursive(false, $previousGroupParameters, $parameters);

        $callback($this);

        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupParameters = $previousGroupParameters;
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function get(string $path, $handler): RouteInterface
    {
        return $this->addRoute([RouteInterface::METHOD_GET], $path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function head(string $path, $handler): RouteInterface
    {
        return $this->addRoute([RouteInterface::METHOD_HEAD], $path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function post(string $path, $handler): RouteInterface
    {
        return $this->addRoute([RouteInterface::METHOD_POST], $path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function put(string $path, $handler): RouteInterface
    {
        return $this->addRoute([RouteInterface::METHOD_PUT], $path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function delete(string $path, $handler): RouteInterface
    {
        return $this->addRoute([RouteInterface::METHOD_DELETE], $path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function options(string $path, $handler): RouteInterface
    {
        return $this->addRoute([RouteInterface::METHOD_OPTIONS], $path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return Route
     */
    public function patch(string $path, $handler): RouteInterface
    {
        return $this->addRoute([RouteInterface::METHOD_PATCH], $path, $handler);
    }

    public function getData(): array
    {
        return $this->dataGenerator->getData();
    }

    /**
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRouter(): RouterInterface
    {
        foreach ($this->routes as $route) {
            $this->routeCompiler->compile($route);
        }

        return $this->routerFactory->create($this);
    }

    protected function normalizedPath(string $path): string
    {
        // add slash to the begin of prefix
        if (0 !== mb_strpos($path, '/')) {
            $path = '/' . $path;
        }

        // remove slash from the end of prefix
        return rtrim($path, '/');
    }

    protected function addGroupParametersToRoute(Route $route): void
    {
        $middlewares = (array)($this->currentGroupParameters['middleware'] ?? []);

        foreach ($middlewares as $middleware) {
            $route->withMiddleware($middleware);
        }
    }
}
