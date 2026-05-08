<?php

namespace Thunder\Console;

interface CommandInterface{
    public function getName():string;
    public function getDescription():string;
    public function handle():void;
}