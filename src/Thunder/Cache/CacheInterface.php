<?php
namespace Thunder\Cache;

interface CacheInterface{
    public function get(string $key):mixed;
    public function set(string $key, mixed $value, ?int $ttl = null):void;
    public function forget(string $key):void;
    public function remember(string $key, callable $callback, ?int $ttl = null):mixed;
}