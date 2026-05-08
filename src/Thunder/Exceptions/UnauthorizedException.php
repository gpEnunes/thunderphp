<?php

namespace Thunder\Exceptions;

class UnauthorizedException extends HttpException
{
    public function __construct(
        string $message = '',
    )
    {
        parent::__construct(401,$message);
    }
}