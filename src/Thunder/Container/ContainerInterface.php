<?php
namespace Thunder\Container;

interface ContainerInterface {
    public function bind(string $abstract, string $concrete): void;
    public function singleton(string $abstract, string $concrete): void;
    public function make(string $abstract):mixed;
}
