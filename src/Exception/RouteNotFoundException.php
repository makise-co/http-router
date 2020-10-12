<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Exception;

class RouteNotFoundException extends RoutingException
{
    public function __construct()
    {
        parent::__construct('Not Found', 0, null);
    }
}
