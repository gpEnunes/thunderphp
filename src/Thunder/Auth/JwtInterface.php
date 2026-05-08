<?php
namespace Thunder\Auth;

interface JwtInterface {
    public function encode(array $payload):string;
    public function decode(string $token):array;
}