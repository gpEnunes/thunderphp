<?php

namespace Thunder\Routing;
use Thunder\Exceptions\NotFoundException;
class Router implements RouterInterface
{
    private array $routes = [];
    private string $currentPrefix = '';
    public function get(string $uri, array $handler):void
    {
        $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, array $handler):void
    {
        $this->addRoute('POST', $uri, $handler);
    }

    public function put(string $uri, array $handler):void
    {
        $this->addRoute('PUT', $uri, $handler);
    }

    public function patch(string $uri, array $handler):void
    {
        $this->addRoute('PATCH', $uri, $handler);
    }
    public function delete(string $uri, array $handler):void
    {
        $this->addRoute('DELETE', $uri, $handler);
    }
    private function addRoute(string $method, string $uri, array $handler):void
    {
        $uri = $this->currentPrefix . $uri;
        $converted = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $uri);
        $pattern = '#^' . $converted . '$#';
        $this->routes[$method][] = ['handler' => $handler, 'pattern' => $pattern];
    }
    public function group(string $prefix, callable $callback):void
    {
        $old = $this->currentPrefix;
        $this->currentPrefix = $old . $prefix;
        $callback($this);
        $this->currentPrefix = $old;
    }
    public function resolve(string $method, string $uri):array{
        foreach ($this->routes[$method] ?? [] as $route){
            if(preg_match($route['pattern'], $uri, $matches)){
                $filteredMatches = array_filter($matches, fn($k) => is_string($k),
                    ARRAY_FILTER_USE_KEY);
                return ['handler' => $route['handler'], 'params' => $filteredMatches];
            }
        }
        throw new NotFoundException("No route for $method $uri found.");
    }

}