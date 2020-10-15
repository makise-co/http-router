# PSR HTTP Router
A PSR HTTP Router based on FastRoute with Middleware support

## Requirements
* PHP >= 7.4

## Installation
`composer require makise-co/http-router`

## Features
* Middlewares
* Dependency Injection to Route Handlers (through PSR Container and [php-di/invoker](https://github.com/PHP-DI/Invoker))
* Supports all of most common request handler declarations (Closures, callables, array-callables, class@method, class::method)
* Strict PSR standards following (each route handler **MUST** return instance of PSR ResponseInterface)
* High portability and customization (any implementation part can be replaced with your own implementation)

## FAQ
* How to get the Route instance that is processing the request?

    ```php
    $route = $request->getAttribute(RouteInterface::class);
    ```
  
* How to get route arguments?

    ```php
    // method 1 - Using DependencyInjection, many frameworks provides this way
    function apiEndpoint(int $id): \Psr\Http\Message\ResponseInterface
    {
    }
  
    // method 2 - Manually getting route arguments
    function apiEndpoint(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $args = $request->getAttribute(Router::ROUTE_ARGS);
    }
    ```

## Performance
For three million calls (using php-di/invoker):
```
Time took: 4.95980096 secs (0.00000165 secs per request)
```

For three million calls (using native function call implementation without DI):
```
Time took: 3.31491995 secs (0.00000110 secs per request)
```

Benchmark code can be found [here](examples/benchmark.php).
* Benchmarks were performed on PHP 7.4 with OPcache enabled
* CPU: Intel Core i7-9750H 6 cores (CPU frequency during benchmarks: 4.07 GHz)
* OS: Ubuntu 20.04 (WSL 2)

## Usage

```php
<?php

declare(strict_types=1);

use DI\Container; // Any PSR container implementation
use MakiseCo\Http\Router\RouteCollectorFactory;
use MakiseCo\Http\Router\RouteCollectorInterface;

$collector = (new RouteCollectorFactory())->create(
    // container that is used to resolve route handlers
    // and inject dependencies into route handlers
    new Container(),
);

$collector->get('/', function (): Response {
    return new Response(200, [], 'Welcome');
});

$collector->addGroup(
    'api',
    [
        'namespace' => 'App\\Http\\Controllers\\',
        'middleware' => [AddHeaderMiddleware1::class]
    ],
    function (RouteCollectorInterface $collector) {
        $collector
            ->get('/balance', function (): Response {
                return new Response(200, [], '228');
            })
            ->setName('api_balance')
            ->withMiddleware(AddHeaderMiddleware2::class)
            // or
            ->withMiddleware(new AddHeaderMiddleware2());

        $collector->post('/posts', function (ServerRequestInterface $request): Response {
            $body = $request->getParsedBody();

            return new Response(200, [], json_encode($body));
        });

        $collector->get('/posts/{id:\d+}', function (int $id): Response {
            return new Response(200, [], "Post {$id}");
        });

        $collector->get('/user', 'UserController@index');
        // or
        $collector->get('/user', [$userController, 'index']);
        // or
        $collector->get('/user', 'UserController::index');
        // or
        $collector->get('/user', [UserController::class, 'index']);
    }
);

$router = $collector->getRouter();

$request = new ServerRequest('GET', '/api/balance');
$response = $router->handle($request);
```

## Error handling
```php
<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use MakiseCo\Http\Router\Exception\MethodNotAllowedException;
use MakiseCo\Http\Router\Exception\RouteNotFoundException;
use MakiseCo\Middleware\ErrorHandlerInterface;
use MakiseCo\Middleware\ErrorHandlingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// It is just an example implementation (a more proper way is to use a response factory)
class ErrorHandler implements ErrorHandlerInterface
{
    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        if ($e instanceof RouteNotFoundException) {
            return new Response(404, [], $e->getMessage());
        }

        if ($e instanceof MethodNotAllowedException) {
            // following https://tools.ietf.org/html/rfc7231#section-6.5.5
            return new Response(405, ['Allow' => $e->getAllowedMethods()], $e->getMessage());
        }

        return new Response(500, [], "Internal Server Error<br><br>{$e->getMessage()}");
    }
}

$router = $collector->getRouter();
$app = (new \MakiseCo\Middleware\MiddlewarePipeFactory())->create([
    new ErrorHandlingMiddleware(new ErrorHandler()), // placing error handling middleware first
    $router
]);
// or
$app = new \MakiseCo\Middleware\Dispatcher([
    new ErrorHandlingMiddleware(new ErrorHandler()), // placing error handling middleware first
    $router
]);
// or use any other middleware dispatcher

$response = $app->handle($request);
```

More complete examples can be found [here](examples/collector.php).
