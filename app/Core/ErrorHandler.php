<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler(function (Throwable $e): void {
            $status = 500;
            $payload = [
                'success' => false,
                'error' => [
                    'message' => 'Error interno',
                ],
            ];

            if ($e instanceof HttpException) {
                $status = $e->statusCode;
                $payload['error']['message'] = $e->getMessage();
                if ($e->details !== []) {
                    $payload['error']['details'] = $e->details;
                }
            } else {
                $isDebug = Env::get('APP_DEBUG', 'false') === 'true';
                if ($isDebug) {
                    $payload['error']['message'] = $e->getMessage();
                    $payload['error']['details'] = [
                        'type' => $e::class,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];
                }
            }

            Response::json($payload, $status)->send();
        });

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}

