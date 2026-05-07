<?php
namespace Thunder\Queue;

interface QueueInterface{
    public function push(object $job):void;
    public function pop(): ?object;
    public function size(): int;
}