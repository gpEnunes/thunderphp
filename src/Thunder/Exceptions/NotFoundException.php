<?php

namespace Thunder\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(
        string $message = '',
    )
    {
        parent::__construct(404, $message);
    }
}