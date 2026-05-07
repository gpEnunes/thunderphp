<?php
namespace Thunder\Routing;
interface RouterInterface
{
    public function get(string $uri, array $handler):void;

    public function post(string $uri, array $handler):void;

    public function resolve(string $method, string $uri):array;
}