# http-router
A HTTP Router based on FastRoute

## Requirements
* PHP >= 7.4

## Features
* Middlewares
* Dependency Injection to Route Handlers (through PSR Container and [php-di/invoker](https://github.com/PHP-DI/Invoker))
* Supports all of most common request handler declarations (Closures, callables, array-callables, class@method, class::method)
* Strict PSR standards following (each route handler **MUST** return instance of PSR ResponseInterface)
* High portability and customization

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

## Usage
```php
<?php

declare(strict_types=1);

use DI\Container; // Any PSR container implementation
use MakiseCo\Http\Router\RouteCollectorFactory;
use MakiseCo\Http\Router\RouteCollectorInterface;
use MakiseCo\Http\Router\Router;

$collector = (new RouteCollectorFactory())->create(
    new Container(), // container that is used to resolve Request Handlers (e.g. Controllers)
    new Container() // container that is used to inject dependencies into route handlers
);

$collector->get('/', function (): Response {
    return new Response(200, [], 'Welcome');
});

$collector->addGroup(
    'api',
    ['middleware' => [AddHeaderMiddleware1::class]],
    function (RouteCollectorInterface $collector) {
        $collector
            ->get('/balance', function (): Response {
                return new Response(200, [], '228');
            })
            ->setName('api_balance')
            ->addMiddleware(AddHeaderMiddleware2::class);

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
    }
);

$router = new Router(
    new FastRoute\Dispatcher\GroupCountBased($collector->getData()),
);

$request = new ServerRequest('GET', '/api/balance');
$response = $router->handle($request);
```

More complete example can be found [here](examples/collector.php).
