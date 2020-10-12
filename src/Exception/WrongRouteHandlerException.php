<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Exception;

use InvalidArgumentException;
use Throwable;

class WrongRouteHandlerException extends InvalidArgumentException
{
    /**
     * @var mixed
     */
    private $handler;

    /**
     * @param string $message
     * @param mixed $handler
     * @param Throwable|null $previous
     */
    public function __construct(string $message, $handler, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param mixed $handler
     * @return self
     */
    public static function forWrongReturnType($handler): self
    {
        return new self(
            'Handler must declare its return type to the ResponseInterface or its implementation (not null)',
            $handler
        );
    }
}
