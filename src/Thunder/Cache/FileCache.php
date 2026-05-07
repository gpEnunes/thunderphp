<?php

namespace Thunder\Cache;

class FileCache implements CacheInterface
{
    public function __construct(private readonly string $cachePath) {}

    private function filePath(string $key): string{
        return $this->cachePath . DIRECTORY_SEPARATOR . md5($key) . '.cache';
    }

    public function get(string $key):mixed{
        $path = $this->filePath($key);
        if (!file_exists($path)) return null;

        $data = unserialize(file_get_contents($path));
        if ($data['expires_at'] !== null && $data['expires_at'] < time()) {
            $this->forget($key);
            return null;
        }
        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null):void{
        $data = ['expires_at' => $ttl ? time() + $ttl: null, 'value' => $value];
        file_put_contents($this->filePath($key), serialize($data));
    }

    public function forget(string $key):void{
        $path = $this->filePath($key);
        if (file_exists($path)) unlink($path);
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);
        if($value !== null) return $value;

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }
}