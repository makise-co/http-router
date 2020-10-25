<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddHeaderMiddleware2 implements MiddlewareInterface
{
    protected string $header = 'Test';
    protected string $value = '123';

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        return $response
            ->withHeader($this->header, $this->value);
    }
}

class AddHeaderMiddleware1 extends AddHeaderMiddleware2
{
    protected string $header = 'Group';
    protected string $value = '456';
}
