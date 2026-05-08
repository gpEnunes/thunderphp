<?php

namespace Thunder\Exceptions;

class TooManyRequestsException extends HttpException
{
    public function __construct(
        string $message = '',
    )
    {
        parent::__construct(429, $message);
    }
}