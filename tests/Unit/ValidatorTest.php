<?php

use Thunder\Validation\Validator;
use Thunder\Exceptions\ValidationException;

test('required fails when field is missing', function () {
    $validator = new Validator([]);
    expect(fn() => $validator->validate(['name' => 'required']))
        ->toThrow(ValidationException::class);
});

test('required passes when field is present', function () {
    $validator = new Validator(['name' => 'John Doe']);
    expect(fn() => $validator->validate(['name' => 'required']))
        ->not->toThrow(ValidationException::class);
});

test('email fails with invalid email', function () {
    $validator = new Validator(['email' => 'not-an-email']);
    expect(fn() => $validator->validate(['email' => 'email']))
        ->toThrow(ValidationException::class);
});

test('email passes with valid email', function () {
    $validator = new Validator(['email' => 'user@example.com']);
    expect(fn() => $validator->validate(['email' => 'email']))
        ->not->toThrow(ValidationException::class);
});

test('min passes with min value', function () {
    $validator = new Validator(['number' => '123']);
    expect(fn() => $validator->validate(['number' => 'min:3']))
        ->not->toThrow(ValidationException::class);
});

test('min fails with not min value', function () {
    $validator = new Validator(['number' => '12']);
    expect(fn() => $validator->validate(['number' => 'min:3']))
        ->toThrow(ValidationException::class);
});

test('max passes with max value', function () {
    $validator = new Validator(['word' => 'John']);
    expect(fn() => $validator->validate(['word' => 'max:6']))
        ->not->toThrow(ValidationException::class);
});

test('max fails with not min value', function () {
    $validator = new Validator(['word' => 'John doe']);
    expect(fn() => $validator->validate(['word' => 'max:3']))
        ->toThrow(ValidationException::class);
});

test('numeric passes', function () {
    $validator = new Validator(['number' => 1233]);
    expect(fn() => $validator->validate(['number' => 'numeric']))
        ->not->toThrow(ValidationException::class);
});

test('numeric fails', function () {
    $validator = new Validator(['number' => '1233aa']);
    expect(fn() => $validator->validate(['number' => 'numeric']))
        ->toThrow(ValidationException::class);
});

test('confirmed passes', function () {
    $validator = new Validator(['password' => '123!', 'password_confirmation' => '123!']);
    expect(fn() => $validator->validate(['password' => 'confirmed']))
        ->not->toThrow(ValidationException::class);
});

test('confirmed fails', function () {
    $validator = new Validator(['password' => '123!', 'password_confirmation' => '222222!']);
    expect(fn() => $validator->validate(['password' => 'confirmed']))
        ->toThrow(ValidationException::class);
});

