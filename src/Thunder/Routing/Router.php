<?php

namespace Thunder\Routing;
class Router implements RouterInterface
{
    private array $routes = [];
    public function get(string $uri, array $handler): void
    {
        $this->routes['GET'][$uri] = $handler;
    }

    public function post(string $uri, array $handler): void{
        $this->routes['POST'][$uri] = $handler;
    }

    public function resolve(string $method, string $uri):array{
        $handler = $this->routes[$method][$uri] ?? null;
        if($handler === null){
            throw new \RuntimeException("No route for $method $uri");
        }
        return $handler;
    }

}