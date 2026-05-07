<?php

use App\Controllers\TestController;
use \Thunder\Routing\RouterInterface;
return function(RouterInterface $router){
    $router->get('/', [TestController::class, 'index']);
};