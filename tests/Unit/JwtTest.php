<?php
use Thunder\Auth\Jwt;

test('encode produces a 3-part token', function () {
  $jwt = new Jwt('secret');
  $token = $jwt->encode(['sub' => 1]);
  expect(count(explode('.', $token)))->toBe(3);
});

test('decode returns original payload', function () {
  $jwt = new Jwt('secret');
  $token = $jwt->encode(['sub' => 1]);
  $payload = $jwt->decode($token);
  expect($payload['sub'])->toBe(1);
});

test('tampered token throws a RuntimeException', function () {
    $jwt = new Jwt('secret');
    $token = $jwt->encode(['sub' => 1]);
    expect(fn() => $jwt->decode($token . 'x'))
        ->toThrow(\RuntimeException::class);
});