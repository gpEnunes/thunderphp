<?php
namespace Thunder\Events;

interface DispatcherInterface{
    public function listen(string $eventClass, string $listenerClass):void;
    public function dispatch(object $event): void;
    public function subscribe(string $subscriberClass):void;
}