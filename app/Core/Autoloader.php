<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    private string $baseDir;
    private string $prefix = 'App\\';

    public function __construct(string $baseDir)
    {
        $this->baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);
    }

    public function register(): void
    {
        spl_autoload_register(function (string $class): void {
            if (!str_starts_with($class, $this->prefix)) {
                return;
            }

            $relative = substr($class, strlen($this->prefix));
            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            $file = $this->baseDir . DIRECTORY_SEPARATOR . $relativePath;

            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}

