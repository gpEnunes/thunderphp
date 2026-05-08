<?php

namespace Thunder\Database;

abstract class Factory
{
    protected \Faker\Generator $faker;
    public function __construct(){
        $this->faker = \Faker\Factory::create();
    }

    abstract public function definition(): array;
    abstract public function modelClass(): string;

    public function make(int $count = 1): mixed{
        if ($count === 1) return $this->build();
        return array_map(fn() => $this->build(), range(1, $count));
    }

    private function build(): object{
        $class = $this->modelClass();
        return new $class(...$this->definition());
    }
}