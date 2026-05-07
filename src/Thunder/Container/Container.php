<?php

namespace Thunder\Container;

class Container implements ContainerInterface
{

    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $abstract, string $concrete): void{
        $this->bindings[$abstract] = $concrete;
    }
    public function singleton(string $abstract, string $concrete): void{
        $this->singletons[$abstract] = $concrete;
    }

    public function make(string $abstract):mixed{
        // 1. Return cached singleton if it exists
        if(isset($this->instances[$abstract])){
            return $this->instances[$abstract];
        }

        // 2. Find the concrete class to build
        // Check singletons first, then bindings, fallback to the abstract itself
        $concrete = $this->singletons[$abstract]
            ?? $this->bindings[$abstract]
            ?? $abstract;

        // 3. Create a reflection mirror of the class
        $reflection = new \ReflectionClass($concrete);

        // 4. Get the constructor - if none, just instantiate directly
        $constructor = $reflection->getConstructor();
        if($constructor === null){
            return new $concrete;
        }

        // 5. Loop through each constructor parameter and resolve it
        $dependencies = [];
        foreach($constructor->getParameters() as $param){
            $type = $param->getType()->getName(); //e.g. "Thunder\Database\Connection"
            $dependencies[] = $this->make($type); //recursive - resolves the dependency too
        }

        // 6. Build the object with all resolved dependencies
        $instance = $reflection->newInstanceArgs($dependencies);

        // 7 . If registered as singleton, cache it for future calls
        if(isset($this->singletons[$abstract])){
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }
}