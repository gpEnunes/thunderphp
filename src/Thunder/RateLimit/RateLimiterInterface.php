<?php
namespace Thunder\RateLimit;

interface RateLimiterInterface{
    public function hit(string $key, int $maxAttempts, int $windowSeconds):void;
    public function tooManyAttempts(string $key, int $maxAttempts):bool;
    public function resetAttempts(string $key):void;
}