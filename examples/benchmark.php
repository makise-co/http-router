<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'helpers/route-collector-native-call.php';

use DI\Container;
use MakiseCo\Http\Router\RouteCollectorFactory;

$response = new \GuzzleHttp\Psr7\Response(200, [], 'Hello World');
$responseHandler = static function () use ($response): \Psr\Http\Message\ResponseInterface {
    return $response;
};

$collector = (new RouteCollectorFactory())->create(
    new Container(),
);

$collector->get('/', $responseHandler);
$collector->get('/admin/users', $responseHandler);
$collector->get('/admin/users/{id:\d+}', $responseHandler);

$iterCount = 1_000_000;

$requestRoot = new \GuzzleHttp\Psr7\ServerRequest('GET', '/');
$requestAdminUsers = new \GuzzleHttp\Psr7\ServerRequest('GET', '/admin/users');
$requestAdminUsersGet = new \GuzzleHttp\Psr7\ServerRequest('GET', '/admin/users/1');

$router = $collector->getRouter();

$start = microtime(true);

for ($i = 0; $i < $iterCount; $i++) {
    $router->handle($requestRoot);
    $router->handle($requestAdminUsers);
    $router->handle($requestAdminUsersGet);
}

$end = microtime(true);

$time = $end - $start;

printf("php-di/invoker:\n");
printf("Time took: %.8f secs (%.8f secs per request)\n\n", $time, $time / ($iterCount * 3));

$collector = getNativeCallRouteCollector(
    new Container(),
);

$collector->get('/', $responseHandler);
$collector->get('/admin/users', $responseHandler);
$collector->get('/admin/users/{id:\d+}', $responseHandler);

$iterCount = 1_000_000;

$requestRoot = new \GuzzleHttp\Psr7\ServerRequest('GET', '/');
$requestAdminUsers = new \GuzzleHttp\Psr7\ServerRequest('GET', '/admin/users');
$requestAdminUsersGet = new \GuzzleHttp\Psr7\ServerRequest('GET', '/admin/users/1');

$router = $collector->getRouter();

$start = microtime(true);

for ($i = 0; $i < $iterCount; $i++) {
    $router->handle($requestRoot);
    $router->handle($requestAdminUsers);
    $router->handle($requestAdminUsersGet);
}

$end = microtime(true);

$time = $end - $start;

printf("native invoker (without DI):\n");
printf("Time took: %.8f secs (%.8f secs per request)\n\n", $time, $time / ($iterCount * 3));

