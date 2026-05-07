<?php

namespace App\Controllers;

use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;
use Thunder\Http\Response;
class TestController
{
    public function index(RequestInterface $request): ResponseInterface{
        return Response::json(['status' => 'ThunderPhp is alive']);
    }
}