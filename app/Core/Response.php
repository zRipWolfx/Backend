<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private array|string $payload;
    private int $statusCode;
    private array $headers;

    private function __construct(array|string $payload, int $statusCode, array $headers = [])
    {
        $this->payload = $payload;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function json(array $payload, int $statusCode = 200, array $headers = []): self
    {
        $headers = array_merge(['Content-Type' => 'application/json; charset=utf-8'], $headers);
        return new self(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $statusCode, $headers);
    }

    public static function html(string $html, int $statusCode = 200, array $headers = []): self
    {
        $headers = array_merge(['Content-Type' => 'text/html; charset=utf-8'], $headers);
        return new self($html, $statusCode, $headers);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->payload;
    }
}
