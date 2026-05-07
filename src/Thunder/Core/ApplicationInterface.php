<?php
namespace Thunder\Core;
use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;

interface ApplicationInterface{
    public function handle(RequestInterface $request):ResponseInterface;
}