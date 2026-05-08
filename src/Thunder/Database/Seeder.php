<?php

namespace Thunder\Database;

abstract class Seeder
{
    abstract public function run(): void;
}