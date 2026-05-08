<?php

namespace Thunder\Console;

use Thunder\Console\CommandInterface;

class Kernel
{
    private array $commands = [];

    public function register(CommandInterface $command):void
    {
        $this->commands[] = $command;
    }
    public function run(array $argv):void
    {
        if($argv[1] ?? null){
            foreach($this->commands as $command){
                if($argv[1] === $command->getName()){
                    $command->handle();
                }
            }
        }else{
            foreach ($this->commands as $command){
                echo $command->getName() . ' - '. $command->getDescription();
            }
        }
    }
}