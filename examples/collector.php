<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'error_handler.php';
require_once 'middlewares.php';

use DI\Container;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use MakiseCo\Http\Router\RouteCollectorFactory;
use MakiseCo\Http\Router\RouteCollectorInterface;
use MakiseCo\Http\Router\Router;
use MakiseCo\Middleware\ErrorHandlingMiddleware;
use MakiseCo\Middleware\MiddlewarePipeFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(
            200,
            ['URI' => $request->getRequestTarget()],
            'UserController Index Method'
        );
    }
}

$collector = (new RouteCollectorFactory())->create(
    new Container(),
    new Container()
);

$collector->addRoute(['GET'], '/user', function (): Response {
    return new Response(200, [], 'Hello');
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
    }
);

$router = new Router(
    new FastRoute\Dispatcher\GroupCountBased($collector->getData()),
);
$app = (new MiddlewarePipeFactory())->create([
    new ErrorHandlingMiddleware(new ErrorHandler()), // placing error handling middleware first
    $router
]);

function sendRequest(ServerRequestInterface $request): void
{
    global $app;

    $response = $app->handle($request);

    printf("Executed %s %s\n", $request->getMethod(), $request->getRequestTarget());
    printf(
        "Status: %d, Headers: %s, Content: %s",
        $response->getStatusCode(),
        json_encode($response->getHeaders()),
        $response->getBody()->getContents()
    );
    printf("\n\n");
}

$request = new ServerRequest('GET', '/user');
sendRequest($request);

$request = new ServerRequest('GET', '/api/balance');
sendRequest($request);

$request = (new ServerRequest(
    'POST',
    '/api/posts',
    ['Content-Type' => 'application/x-www-form-urlencoded'],
))->withParsedBody(
    [
        'title' => 'Article 1',
        'content' => 'Article about PHP',
        'author_id' => 1,
    ]
);
sendRequest($request);

$request = new ServerRequest('GET', '/api/posts/123');
sendRequest($request);

$request = new ServerRequest('GET', '/api/user');
sendRequest($request);

$request = new ServerRequest('GET', '/api/not-found');
sendRequest($request);

$request = new ServerRequest('POST', '/api/user');
sendRequest($request);
