<?php

namespace Thunder\Exceptions;

class ValidationException extends HttpException
{
    public function __construct(
        string $message = '',
    )
    {
        parent::__construct(422, $message);
    }
}