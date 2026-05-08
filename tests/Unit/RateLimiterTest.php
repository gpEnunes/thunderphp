<?php
use Thunder\RateLimit\RateLimiter;
use Thunder\Cache\ArrayCache;
use Thunder\Exceptions\TooManyRequestsException;

test('first hit passes without throwing', function () {
  $limiter = new RateLimiter(new ArrayCache());
  expect(fn() => $limiter->hit('test', 3, 60))
      ->not->toThrow(TooManyRequestsException::class);
});

test('hitting beyond the limits throws TooManyRequestsException', function () {
    $limiter = new RateLimiter(new ArrayCache());
    expect(function () use ($limiter) {
        for ($i = 0; $i <= 3; $i++) {
            $limiter->hit('test', 3, 60);
        }
    })->toThrow(TooManyRequestsException::class);
});

test('reset clears the counter', function () {
    $limiter = new RateLimiter(new ArrayCache());

    // hit the limit
    for ($i = 0; $i <= 3; $i++) {
        try { $limiter->hit('test', 3, 60); } catch (TooManyRequestsException) {}
    }

    // reset and assert next hit passes
    $limiter->resetAttempts('test');
    expect(fn() => $limiter->hit('test', 3, 60))
        ->not->toThrow(TooManyRequestsException::class);
});