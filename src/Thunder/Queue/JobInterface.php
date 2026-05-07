<?php
namespace Thunder\Queue;

interface JobInterface{
    public function handle(): void;
}