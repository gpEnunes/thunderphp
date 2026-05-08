<?php

namespace Thunder\RateLimit;

use Thunder\Cache\CacheInterface;
use Thunder\Exceptions\TooManyRequestsException;

class RateLimiter implements RateLimiterInterface
{
    public function __construct(private readonly CacheInterface $cache){}

    public function hit(string $key, int $maxAttempts, int $windowSeconds):void
    {
        $data = $this->cache->get($key);
        if (is_null($data) || empty($data)) {
            $data = ['count' => 1, 'expires_at' => time() + $windowSeconds];
        }else{
            $data['count']++;
        }
        $this->cache->set($key, $data, $windowSeconds);
        if ($data['count'] > $maxAttempts) {
            throw new TooManyRequestsException('Too many requests');
        }
    }
    public function tooManyAttempts(string $key, int $maxAttempts):bool
    {
        $data = $this->cache->get($key);
        return $data !== null && $data['count'] > $maxAttempts;
    }
    public function resetAttempts(string $key):void
    {
        $this->cache->forget($key);
    }
}