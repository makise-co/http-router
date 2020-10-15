<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Tests;

use DI\Container;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use MakiseCo\Http\Router\Exception\MethodNotAllowedException;
use MakiseCo\Http\Router\Exception\RouteNotFoundException;
use MakiseCo\Http\Router\RouteCollector;
use MakiseCo\Http\Router\RouteCollectorFactory;
use MakiseCo\Http\Router\RouteCollectorInterface;
use MakiseCo\Http\Router\RouterInterface;
use PHPStan\Testing\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteCollectorTest extends TestCase
{
    private RouteCollector $collector;

    private \Closure $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collector = (new RouteCollectorFactory())->create(
            new Container(),
        );

        $this->response = static function (ServerRequestInterface $request): Response {
            return new Response(200, [], $request->getRequestTarget());
        };
    }

    /**
     * @testWith        ["GET", "/"]
     *                  ["POST", "/posts"]
     *                  ["PATCH", "/posts/{id:\\d+}", "/posts/1"]
     *                  ["DELETE", "/posts/{id:\\d+}", "/posts/1"]
     *                  ["GET", "/posts/{id:\\d+}", "/posts/1"]
     *                  ["PUT", "/posts/{id:\\d+}", "/posts/1"]
     *                  ["OPTIONS", "/posts/{id:\\d+}", "/posts/1"]
     *                  ["HEAD", "/posts/{id:\\d+}", "/posts/1"]
     *
     * @param string $method
     * @param string $path
     * @param string|null $normPath
     */
    public function testRootRoutes(string $method, string $path, ?string $normPath = null): void
    {
        $this->collector->addRoute(
            $method,
            $path,
            $this->response
        );

        $router = $this->collector->getRouter();

        $request = new ServerRequest($method, $normPath ?? $path);

        $this->assertSendRequest($request, $router);
    }

    public function testRouteGroups(): void
    {
        $this->collector->addGroup('admin', [], function (RouteCollectorInterface $routes) {
            $routes->get('/users', $this->response);
            $routes->get('/users/{id:\d+}', $this->response);

            $routes->addGroup('blog', [], function (RouteCollectorInterface $routes) {
                $routes->post('/posts', $this->response);
                $routes->get('/posts/{id:\d+}', $this->response);
            });
        });

        $router = $this->collector->getRouter();

        $this->assertSendRequest(new ServerRequest('GET', '/admin/users'), $router);
        $this->assertSendRequest(new ServerRequest('GET', '/admin/users/1'), $router);

        $this->assertSendRequest(new ServerRequest('POST', '/admin/blog/posts'), $router);
        $this->assertSendRequest(new ServerRequest('GET', '/admin/blog/posts/1'), $router);
    }

    public function testMiddlewares(): void
    {
        $this->collector
            ->get('/', $this->response)
            ->withMiddleware($this->getFakeMiddleware('root'));

        $this->collector->addGroup(
            'admin',
            ['middleware' => [$this->getFakeMiddleware('group')]],
            function (RouteCollectorInterface $routes) {
                $routes->get('/users', $this->response);

                $routes
                    ->get('/users/{id:\d+}', $this->response)
                    ->withMiddleware($this->getFakeMiddleware('custom'))
                    ->withMiddleware(TmpMiddleware::class);
            }
        );

        $router = $this->collector->getRouter();

        $response = $this->assertSendRequest(new ServerRequest('GET', '/'), $router);
        self::assertSame(['root'], $response->getHeader('MIDDLEWARE'));

        $response = $this->assertSendRequest(new ServerRequest('GET', '/admin/users'), $router);
        self::assertSame(['group'], $response->getHeader('MIDDLEWARE'));

        $response = $this->assertSendRequest(new ServerRequest('GET', '/admin/users/1'), $router);
        self::assertSame(['tmp', 'custom', 'group'], $response->getHeader('MIDDLEWARE'));
    }

    public function testRouteNotFound(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $this->collector->getRouter()->handle(new ServerRequest('GET', '/'));
    }

    public function testMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->collector->post('/', $this->response);

        $this->collector->getRouter()->handle(new ServerRequest('GET', '/'));
    }

    protected function assertSendRequest(ServerRequest $request, RouterInterface $router): ResponseInterface
    {
        $response = $router->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame($request->getUri()->getPath(), $response->getBody()->getContents());

        return $response;
    }

    protected function getFakeMiddleware(string $name): MiddlewareInterface
    {
        return new class($name) implements MiddlewareInterface {
            private string $name;

            public function __construct(string $name)
            {
                $this->name = $name;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response = $handler->handle($request);

                return $response->withAddedHeader('MIDDLEWARE', $this->name);
            }
        };
    }
}

class TmpMiddleware implements MiddlewareInterface
{
    private string $name = 'tmp';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withAddedHeader('MIDDLEWARE', $this->name);
    }
}
