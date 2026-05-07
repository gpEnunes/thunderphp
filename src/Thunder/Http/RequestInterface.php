<?php
namespace Thunder\Http;

interface RequestInterface{
    public function method(): string;
    public function uri(): string;
    public function input(string $key, mixed $default = null): mixed;
    public function query(string $key, mixed $default = null): mixed;
    public static function fromGlobals():static;
}

