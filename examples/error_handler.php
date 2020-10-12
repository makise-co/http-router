<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Psr7\Response;
use MakiseCo\Http\Router\Exception\MethodNotAllowedException;
use MakiseCo\Http\Router\Exception\RouteNotFoundException;
use MakiseCo\Middleware\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
