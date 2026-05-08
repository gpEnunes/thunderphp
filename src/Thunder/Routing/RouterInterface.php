<?php
namespace Thunder\Routing;
interface RouterInterface
{
    public function get(string $uri, array $handler):void;
    public function post(string $uri, array $handler):void;
    public function put(string $uri, array $handler):void;
    public function patch(string $uri, array $handler):void;
    public function delete(string $uri, array $handler):void;
    public function resolve(string $method, string $uri):array;
    public function group(string $prefix, callable $callback):void;
}