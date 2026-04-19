<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

final class Jwt
{
    public static function encode(array $payload, string $secret, int $ttlSeconds): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ]);

        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $token, string $secret): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new HttpException(401, 'Token inválido');
        }

        [$h64, $p64, $s64] = $parts;
        $header = json_decode(self::base64UrlDecode($h64), true);
        $payload = json_decode(self::base64UrlDecode($p64), true);
        $signature = self::base64UrlDecode($s64);

        if (!is_array($header) || !is_array($payload) || !is_string($signature)) {
            throw new HttpException(401, 'Token inválido');
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new HttpException(401, 'Token inválido');
        }

        $expected = hash_hmac('sha256', $h64 . '.' . $p64, $secret, true);
        if (!hash_equals($expected, $signature)) {
            throw new HttpException(401, 'Token inválido');
        }

        $exp = $payload['exp'] ?? null;
        if (!is_int($exp) && !is_float($exp)) {
            throw new HttpException(401, 'Token inválido');
        }
        if ((int)$exp < time()) {
            throw new HttpException(401, 'Token expirado');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new HttpException(401, 'Token inválido');
        }
        return $decoded;
    }
}

