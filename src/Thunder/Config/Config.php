<?php

namespace Thunder\Config;

class Config implements ConfigInterface
{
    private array $items = [];
    public function __construct(string $configPath){
        foreach(glob($configPath.'/*.php') as $file){
            $key = pathinfo($file, PATHINFO_FILENAME); //e.g. "database"
            $this->items[$key] = require $file;
        }
    }
    public function get(string $key, mixed $default = null): mixed{
        $parts = explode('.', $key);
        $value = $this->items;
        foreach($parts as $part){
            if(!isset($value[$part])) return $default;
            $value = $value[$part];
        }
        return $value;
    }

    public function has(string $key): bool{
        return $this->get($key) !== null;
    }

    public function set(string $key, mixed $value): void{
        $parts = explode('.', $key);
        $target = &$this->items;
        foreach($parts as $part){
            $target = &$target[$part];
        }
        $target = $value;
    }


}