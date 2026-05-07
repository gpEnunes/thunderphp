<?php

namespace Thunder\Core;

use Thunder\Container\ContainerInterface;
use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;
use Thunder\Routing\RouterInterface;

class Application implements ApplicationInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly ContainerInterface $container
    ){}

    public function loadRoutes(string $routesPath):void{
        $routes = require $routesPath;
        $routes($this->router);
    }

    public function handle(RequestInterface $request):ResponseInterface{
        $handler = $this->router->resolve($request->method(), $request->uri());
        [$controllerClass, $method] = $handler;
        $controller = $this->container->make($controllerClass);
        return $controller->$method($request);
    }
}