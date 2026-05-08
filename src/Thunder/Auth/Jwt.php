<?php

namespace Thunder\Auth;

class Jwt implements JwtInterface
{
    public function __construct(private readonly string $secret){}

    private function base64UrlEncode(string $payload):string
    {
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $token):string
    {
        return base64_decode(strtr($token, '-_', '+/'));
    }

    public function encode(array $payload):string{
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$body", $this->secret, true));
        return "$header.$body.$signature";
    }
    public function decode(string $token):array{
        $parts = explode('.', $token);
        if (count($parts) !== 3) throw new \RuntimeException('Invalid token');
        $expected = $this->base64UrlEncode(hash_hmac('sha256', "$parts[0].$parts[1]", $this->secret, true));
        if (!hash_equals($expected, $parts[2])) throw new \RuntimeException('Invalid signature');
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        if (isset($payload['exp']) && $payload['exp'] < time()) throw new \RuntimeException('Token expired');
        return $payload;
    }
}