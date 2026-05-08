<?php

namespace Thunder\RateLimit;
use Thunder\Exceptions\TooManyRequestsException;
use Thunder\Http\RequestInterface;
use Thunder\Http\ResponseInterface;
use Thunder\Http\Response;
use Thunder\Middleware\MiddlewareInterface;
use Thunder\RateLimit\RateLimiterInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RateLimiterInterface $rateLimit,
        private readonly int $maxAttempts = 60,
        private readonly int $windowSeconds = 60
    ){}
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        try {
            $this->rateLimit->hit("rate_limit:$ip", $this->maxAttempts, $this->windowSeconds);
        }catch(TooManyRequestsException $e){
            return Response::json(['error' => 'Too many requests'],429);
        }
        return $next($request);
    }
}