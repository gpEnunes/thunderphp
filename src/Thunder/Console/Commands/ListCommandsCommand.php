<?php

namespace Thunder\Console\Commands;
use Thunder\Console\Command;
class ListCommandsCommand extends Command
{
    protected string $name = 'list';
    protected string $description = 'List all available commands';

    public function handle():void
    {
        echo "List commands:\n";
    }
}