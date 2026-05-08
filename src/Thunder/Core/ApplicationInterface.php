<?php
namespace Thunder\Core;
use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;

interface ApplicationInterface{
    public function loadRoutes(string $routesPath):void;
    public function handle(RequestInterface $request):ResponseInterface;
}