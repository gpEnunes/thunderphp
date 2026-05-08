<?php

namespace Thunder\Exceptions;

class HttpException extends \RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
    )
    {
        parent::__construct($message);
    }

    public function getStatusCode():int {return $this->statusCode;}
}