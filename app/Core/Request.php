<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public string $method;
    public string $path;
    public array $query = [];
    public array $headers = [];
    public array $params = [];
    public mixed $body = null;
    public array $files = [];
    public ?array $user = null;

    public static function fromGlobals(): self
    {
        $req = new self();

        $req->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $req->path = is_string($path) && $path !== '' ? $path : '/';

        $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '') {
            $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            if ($basePath !== '' && $basePath !== '/' && str_starts_with($req->path, $basePath . '/')) {
                $req->path = substr($req->path, strlen($basePath));
            } elseif ($basePath !== '' && $basePath !== '/' && $req->path === $basePath) {
                $req->path = '/';
            }
        }

        $req->path = rtrim($req->path, '/');
        if ($req->path === '') {
            $req->path = '/';
        }

        $req->query = $_GET ?? [];
        $req->headers = self::readHeaders();
        $req->files = $_FILES ?? [];

        $contentType = $req->headers['content-type'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw ?: '', true);
            $req->body = is_array($decoded) ? $decoded : null;
        } elseif (in_array($req->method, ['POST', 'PUT', 'PATCH'], true)) {
            $req->body = $_POST ?: null;
        }

        return $req;
    }

    private static function readHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $name = strtolower(str_replace('_', '-', $key));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
