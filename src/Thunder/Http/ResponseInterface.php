<?php

namespace Thunder\Http;

interface ResponseInterface{
    public function send() :void;
    public function status():int;
    public function headers(string $key, mixed $default = null): mixed;
    public function body():string;
    public static function json(mixed $data, int $status = 200): static;

}