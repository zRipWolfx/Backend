<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ConfiguracionRepository;

final class ConfiguracionController
{
    private ConfiguracionRepository $configuracion;

    public function __construct()
    {
        $this->configuracion = new ConfiguracionRepository();
    }

    public function show(Request $request): Response
    {
        $row = $this->configuracion->createDefaultIfMissing();
        return Response::json(['success' => true, 'data' => $row], 200);
    }

    public function update(Request $request): Response
    {
        $current = $this->configuracion->createDefaultIfMissing();

        $body = Validator::requireArray($request->body);
        $data = [
            'color_primario' => Validator::optionalString($body, 'color_primario', 20) ?? (string)$current['color_primario'],
            'color_secundario' => Validator::optionalString($body, 'color_secundario', 20) ?? (string)$current['color_secundario'],
            'moneda' => Validator::optionalString($body, 'moneda', 10) ?? (string)$current['moneda'],
            'igv' => array_key_exists('igv', $body) ? Validator::requiredNumber($body, 'igv') : (float)$current['igv'],
        ];

        $this->configuracion->update((int)$current['id'], $data);
        $updated = $this->configuracion->getFirst();
        return Response::json(['success' => true, 'data' => $updated], 200);
    }
}

