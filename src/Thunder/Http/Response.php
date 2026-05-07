<?php

namespace Thunder\Http;

class Response implements ResponseInterface
{
    public function __construct(
        private readonly int $status = 200,
        private readonly array $headers = [],
        private readonly string $body = '',
    ){}

    public function status(): int{
        return $this->status;
    }
    public function headers(string $key, mixed $default = null): mixed{
        return $this->headers[$key] ?? $default;
    }

    public function body(): string{
        return $this->body;
    }
    public function send():void {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }
    public static function json(mixed $data, int $status = 200): static
    {
        return new static(
            status: $status,
            headers: ['Content-Type' => 'application/json'],
            body: json_encode($data),
        );
    }
}