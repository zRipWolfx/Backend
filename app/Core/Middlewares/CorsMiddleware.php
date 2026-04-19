<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use App\Core\Env;
use App\Core\MiddlewareInterface;
use App\Core\Request;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        $allowed = Env::get('CORS_ORIGINS', '*') ?? '*';
        $origin = $request->headers['origin'] ?? '';

        if ($allowed === '*') {
            header('Access-Control-Allow-Origin: *');
        } else {
            $origins = array_map('trim', explode(',', $allowed));
            if ($origin !== '' && in_array($origin, $origins, true)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Vary: Origin');
            }
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');

        return $next($request);
    }
}

