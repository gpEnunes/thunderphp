<?php

use Thunder\Routing\Router;
use Thunder\Exceptions\NotFoundException;

test('exact route resolves correctly', function () {
    $router = new Router();
    $router->get('/users', [UserController::class, 'index']);
    $result = $router->resolve('GET', '/users');
    expect($result['handler'])->toBe([UserController::class, 'index']);
});

test('unknown route throws NotFoundException', function () {
    $router = new Router();
    expect(fn() => $router->resolve('GET', '/unknown'))
        ->toThrow(NotFoundException::class);
});

test('dynamic params are extracted', function () {
    $router = new Router();
    $router->get('/users/{id}', [UserController::class, 'show']);
    $result = $router->resolve('GET', '/users/42');
    expect($result['params']['id'])->toBe('42');
});

test('route group prefix is prepended', function () {
    $router = new Router();
    $router->group('/api', function (Router $router) {
        $router->get('/users', [UserController::class, 'index']);
    });
    $result = $router->resolve('GET', '/api/users');
    expect($result['handler'])->toBe([UserController::class, 'index']);
});
