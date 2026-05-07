<?php

namespace Thunder\Http;

class Request implements RequestInterface
{
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array $query = [],
        private readonly array $body = [],
    ) {}

    public function method():string{
        return $this->method;
    }

    public function uri():string{
        return $this->uri;
    }

    public function query(string $key, mixed $default = null):mixed{
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed{
        return $this->body[$key] ?? $default;
    }

    public function body():array{
        return $this->body;
    }

    public static function fromGlobals():static{
        return new static(
            method: $_SERVER['REQUEST_METHOD'],
            uri: $_SERVER['REQUEST_URI'],
            query: $_GET,
            body: $_POST,
        );
    }
}