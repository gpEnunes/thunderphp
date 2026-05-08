<?php

namespace Thunder\Exceptions;

class ForbiddenException extends HttpException
{
    public function __construct(
        string $message = '',
    )
    {
        parent::__construct(403,$message);
    }
}