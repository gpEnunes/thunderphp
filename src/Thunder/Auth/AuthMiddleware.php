<?php

namespace Thunder\Auth;
use Thunder\Middleware\MiddlewareInterface;
use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;
use Thunder\Http\Response;
class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly JwtInterface $jwt){}

    public function handle(RequestInterface $request, callable $next):ResponseInterface{
        $authorizationHeader =  $request->header('Authorization');
        if($authorizationHeader === ''){
            return Response::json(['error' => 'Unauthorized'], 401);
        }
        try{
            $token = str_replace('Bearer ', '', $authorizationHeader);
            $this->jwt->decode($token);
        }catch(\RuntimeException $e){
            return Response::json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}