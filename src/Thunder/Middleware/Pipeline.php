<?php

namespace Thunder\Middleware;
use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;
class Pipeline
{
    private array $middlewares = [];

    public function __construct(private readonly RequestInterface $request){}

    public function through(array $middlewares):static{
        $this->middlewares = $middlewares;
        return $this;
    }
    public function run(callable $destination):ResponseInterface {
        $chain = array_reduce(
            array_reverse($this->middlewares),
            fn($next, $middleware) => fn($req) => (new $middleware)->handle($req, $next),
            $destination
        );

        return $chain($this->request);
    }
}