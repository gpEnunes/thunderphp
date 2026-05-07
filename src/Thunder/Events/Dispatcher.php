<?php

namespace Thunder\Events;

class Dispatcher implements DispatcherInterface
{
    private array $listeners = [];

    public function listen(string $eventClass, string $listenerClass): void{
        $this->listeners[$eventClass][] = $listenerClass;
    }

    public function dispatch(object $event): void{
        foreach($this->listeners[$event::class] ?? [] as $listenerClass){
            $listener = new $listenerClass();
            $listener->handle($event);
        }
    }

    public function subscribe(string $subscriberClass): void{
        $subscriber = new $subscriberClass();
        $subscriber->subscribe($this);
    }
}