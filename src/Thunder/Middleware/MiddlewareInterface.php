<?php
namespace Thunder\Middleware;
use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;
interface MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface;
}