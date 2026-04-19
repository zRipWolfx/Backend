<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use App\Core\Middlewares\AuthMiddleware;
use App\Core\Middlewares\CorsMiddleware;

final class App
{
    private string $basePath;
    private Router $router;

    private function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->router = new Router();
    }

    public static function boot(string $basePath): self
    {
        $app = new self($basePath);

        Env::load($app->basePath . DIRECTORY_SEPARATOR . '.env');
        ErrorHandler::register();

        $app->router->middleware(new CorsMiddleware());
        $app->router->middleware(new AuthMiddleware());

        require $app->basePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';

        return $app;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $request = Request::fromGlobals();

        if ($request->method === 'OPTIONS') {
            Response::json(['success' => true], 200)->send();
            return;
        }

        try {
            $result = $this->router->dispatch($request);
            if ($result instanceof Response) {
                $result->send();
                return;
            }

            Response::json(['success' => true, 'data' => $result], 200)->send();
        } catch (HttpException $e) {
            Response::json(
                ['success' => false, 'error' => ['message' => $e->getMessage(), 'details' => $e->details]],
                $e->statusCode
            )->send();
        }
    }
}

