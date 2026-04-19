<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use App\Core\Env;
use App\Core\Exceptions\HttpException;
use App\Core\Jwt;
use App\Core\MiddlewareInterface;
use App\Core\Request;

final class AuthMiddleware implements MiddlewareInterface
{
    private array $publicExactPaths = [
        '/',
        '/docs',
    ];

    private array $publicPrefixes = [
        '/auth/login',
        '/auth/register',
        '/uploads/',
    ];

    public function handle(Request $request, callable $next): mixed
    {
        if (in_array($request->path, $this->publicExactPaths, true)) {
            return $next($request);
        }

        foreach ($this->publicPrefixes as $prefix) {
            if (str_starts_with($request->path, $prefix)) {
                return $next($request);
            }
        }

        $auth = $request->headers['authorization'] ?? '';
        if (!is_string($auth) || !str_starts_with($auth, 'Bearer ')) {
            throw new HttpException(401, 'No autenticado');
        }

        $token = trim(substr($auth, 7));
        if ($token === '') {
            throw new HttpException(401, 'No autenticado');
        }

        $secret = Env::get('JWT_SECRET', '');
        if ($secret === '') {
            throw new HttpException(500, 'JWT_SECRET no configurado');
        }

        $payload = Jwt::decode($token, $secret);
        $request->user = [
            'id' => (int)($payload['sub'] ?? 0),
            'correo' => (string)($payload['correo'] ?? ''),
        ];

        if ($request->user['id'] <= 0) {
            throw new HttpException(401, 'No autenticado');
        }

        return $next($request);
    }
}
