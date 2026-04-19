<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

final class DocsController
{
    public function __construct(private Router $router)
    {
    }

    public function index(Request $request): Response
    {
        $routes = $this->router->listRoutes();

        $rows = '';
        foreach ($routes as $r) {
            $method = htmlspecialchars((string)$r['method'], ENT_QUOTES, 'UTF-8');
            $pattern = htmlspecialchars((string)$r['pattern'], ENT_QUOTES, 'UTF-8');
            $rows .= "<tr><td>{$method}</td><td><code>{$pattern}</code></td></tr>";
        }

        $html = '<!doctype html><html lang="es"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/><title>API Endpoints</title><style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Arial,sans-serif;margin:24px;color:#111}h1{margin:0 0 12px 0;font-size:20px}table{border-collapse:collapse;width:100%;max-width:960px}th,td{border:1px solid #e5e7eb;padding:10px;text-align:left;vertical-align:top}th{background:#f9fafb}code{background:#f3f4f6;padding:2px 6px;border-radius:6px}small{color:#6b7280}</style></head><body>';
        $html .= '<h1>Endpoints disponibles</h1>';
        $html .= '<small>Tip: los endpoints protegidos requieren header Authorization: Bearer &lt;token&gt;</small>';
        $html .= '<table><thead><tr><th>Método</th><th>Ruta</th></tr></thead><tbody>' . $rows . '</tbody></table>';
        $html .= '</body></html>';

        return Response::html($html, 200);
    }
}

