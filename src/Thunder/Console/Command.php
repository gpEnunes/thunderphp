<?php

namespace Thunder\Console;

abstract class Command implements CommandInterface
{
    protected string $name = '';
    protected string $description = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    abstract public function handle(): void;
}