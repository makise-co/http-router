<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use MakiseCo\Http\Router\RouteCollectorFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function MakiseCo\Http\Router\Helper\getRouteAttribute;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public const MATCH_ANY = 'any';
    public const MATCH_ALL = 'all';

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!$this->isAuthorized($request)) {
            return new Response(403, [], 'Access Denied');
        }

        return $handler->handle($request);
    }

    private function isAuthorized(ServerRequestInterface $request): bool
    {
        $userPermissions = $request->getQueryParams()['permissions'] ?? [];
        // in real application you will have something like this:
//        $user = $request->getAttribute(\App\Entity\User::class);
//        $userPermissions = $user->getPermissions();

        $neededPermissions = getRouteAttribute($request, 'auth_permissions', []);

        $matchMode = getRouteAttribute($request, 'auth_mode', self::MATCH_ANY);
        if ($matchMode === self::MATCH_ANY) {
            return $this->matchAny($userPermissions, $neededPermissions);
        }

        if ($matchMode === self::MATCH_ALL) {
            return $this->matchAll($userPermissions, $neededPermissions);
        }

        // unknown auth mode (throw exception, log this or just return false)
        return false;
    }

    private function matchAll(array $userPermissions, array $neededPermissions): bool
    {
        // "AND" matching - all of neededPermissions must be present in user permissions
        foreach ($neededPermissions as $neededPermission) {
            if (!in_array($neededPermission, $userPermissions, true)) {
                return false;
            }
        }

        return true;
    }

    private function matchAny(array $userPermissions, array $neededPermissions): bool
    {
        // "OR" matching - any of neededPermissions must be present in user permissions
        if (empty($neededPermissions)) {
            return true;
        }

        foreach ($neededPermissions as $neededPermission) {
            if (in_array($neededPermission, $userPermissions, true)) {
                return true;
            }
        }

        return false;
    }
}

$collector = (new RouteCollectorFactory())->create(
    new Container(),
);

$collector
    ->get(
        '/admin/posts',
        function (): Response {
            return new Response(200);
        }
    )
    ->withMiddleware(AuthorizationMiddleware::class)
    ->withAttribute('auth_permissions', ['admin', 'moderator'])
    ->withAttribute('auth_mode', 'any');

$router = $collector->getRouter();

$request = new ServerRequest('GET', '/admin/posts');
$response = $router->handle($request);

printf("Request with no permissions status: %d\n", $response->getStatusCode());

// ----

$request = new ServerRequest('GET', '/admin/posts', [], null, [], []);
$request = $request->withQueryParams(['permissions' => ['admin']]);
$response = $router->handle($request);

printf("Request with admin permission status: %d\n", $response->getStatusCode());

// ----

$request = new ServerRequest('GET', '/admin/posts');
$request = $request->withQueryParams(['permissions' => ['moderator']]);
$response = $router->handle($request);

printf("Request with moderator permission status: %d\n", $response->getStatusCode());
