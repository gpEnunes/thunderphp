<?php

namespace Thunder\Cache;

class ArrayCache implements CacheInterface
{
    private array $store = [];

    public function get(string $key):mixed{
        $data = $this->store[$key] ?? null;
        if($data === null) return null;
        if ($data['expires_at'] !== null && $data['expires_at'] < time()) {
            $this->forget($key);
            return null;
        }
        return $data['value'];
    }
    public function set(string $key, mixed $value, ?int $ttl = null):void{
        $this->store[$key] = [
            'expires_at' => $ttl ? time() + $ttl : null,
            'value' => $value,
        ];
    }
    public function forget(string $key):void{
        if(array_key_exists($key, $this->store)) {
            unset($this->store[$key]);
        }
    }
    public function remember(string $key, callable $callback, ?int $ttl = null):mixed{
        $value = $this->get($key);
        if(isset($value)) return $value;
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

}