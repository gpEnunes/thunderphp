<?php
namespace Thunder\Http;

interface RequestInterface{
    public function method(): string;
    public function uri(): string;
    public function input(string $key, mixed $default = null): mixed;
    public function query(string $key, mixed $default = null): mixed;
    public function body():array;
    public function header(string $key, string $default = ''):string;
    public static function fromGlobals():static;
}

