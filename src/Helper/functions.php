<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Helper;

use MakiseCo\Http\Router\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Get route attribute from request
 *
 * @param ServerRequestInterface $request
 * @param string $key
 * @param mixed $default
 *
 * @return mixed
 */
function getRouteAttribute(ServerRequestInterface $request, string $key, $default = null)
{
    /** @var RouteInterface|null $route */
    $route = $request->getAttribute(RouteInterface::class);
    if ($route === null) {
        return $default;
    }

    return $route->getAttribute($key, $default);
}

/**
 * Get route attributes from request
 *
 * @param ServerRequestInterface $request
 *
 * @return array<string, mixed>
 */
function getRouteAttributes(ServerRequestInterface $request): array
{
    /** @var RouteInterface|null $route */
    $route = $request->getAttribute(RouteInterface::class);
    if ($route === null) {
        return [];
    }

    return $route->getAttributes();
}
