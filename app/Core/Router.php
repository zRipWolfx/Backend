<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

final class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function middleware(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function add(string $method, string $pattern, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex' => $this->compilePattern($pattern),
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, callable $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    public function listRoutes(): array
    {
        $out = [];
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route) {
                $out[] = [
                    'method' => $method,
                    'pattern' => (string)($route['pattern'] ?? ''),
                ];
            }
        }

        usort($out, function (array $a, array $b): int {
            $cmp = strcmp((string)$a['pattern'], (string)$b['pattern']);
            if ($cmp !== 0) {
                return $cmp;
            }
            return strcmp((string)$a['method'], (string)$b['method']);
        });

        return $out;
    }

    public function dispatch(Request $request): mixed
    {
        $route = $this->match($request->method, $request->path);
        if ($route === null) {
            throw new HttpException(404, 'Ruta no encontrada');
        }

        $request->params = $route['params'];

        $handler = $route['handler'];

        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            function (callable $next, MiddlewareInterface $middleware): callable {
                return function (Request $request) use ($middleware, $next): mixed {
                    return $middleware->handle($request, $next);
                };
            },
            function (Request $request) use ($handler): mixed {
                return $handler($request);
            }
        );

        return $pipeline($request);
    }

    private function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);
        $candidates = $this->routes[$method] ?? [];
        foreach ($candidates as $route) {
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $params = [];
            foreach ($matches as $k => $v) {
                if (is_string($k)) {
                    $params[$k] = $v;
                }
            }

            return [
                'handler' => $route['handler'],
                'params' => $params,
            ];
        }
        return null;
    }

    private function compilePattern(string $pattern): string
    {
        $pattern = rtrim($pattern, '/');
        if ($pattern === '') {
            $pattern = '/';
        }

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}
