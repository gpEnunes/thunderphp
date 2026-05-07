<?php

namespace Thunder\Core;

use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;
use Thunder\Routing\RouterInterface;

class Application implements ApplicationInterface
{
    public function __construct(private readonly RouterInterface $router){}

    public function handle(RequestInterface $request):ResponseInterface{
        $handler = $this->router->resolve($request->method(), $request->uri());
        [$controllerClass, $method] = $handler;
        $controller = new $controllerClass();
        return $controller->$method($request);
    }
}