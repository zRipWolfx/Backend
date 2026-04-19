<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\CotizacionRepository;
use App\Services\CotizacionService;
use App\Repositories\DetalleCotizacionRepository;
use App\Repositories\ProductoRepository;
use App\Repositories\ConfiguracionRepository;

final class CotizacionesController
{
    private CotizacionRepository $cotizaciones;
    private CotizacionService $service;

    public function __construct()
    {
        $this->cotizaciones = new CotizacionRepository();
        $this->service = new CotizacionService(
            new CotizacionRepository(),
            new DetalleCotizacionRepository(),
            new ProductoRepository(),
            new ConfiguracionRepository()
        );
    }

    public function index(Request $request): Response
    {
        $rows = $this->cotizaciones->list();
        return Response::json(['success' => true, 'data' => $rows], 200);
    }

    public function show(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }
        $row = $this->service->getWithDetalle($id);
        return Response::json(['success' => true, 'data' => $row], 200);
    }

    public function create(Request $request): Response
    {
        $body = Validator::requireArray($request->body);

        $payload = [
            'cliente_id' => Validator::optionalInt($body, 'cliente_id'),
            'contacto_id' => Validator::optionalInt($body, 'contacto_id'),
            'fecha_emision' => Validator::requiredString($body, 'fecha_emision', 8, 10),
            'moneda' => Validator::optionalString($body, 'moneda', 10),
            'forma_pago' => Validator::optionalString($body, 'forma_pago', 100),
            'plazo_entrega' => Validator::optionalString($body, 'plazo_entrega', 100),
            'garantia' => Validator::optionalString($body, 'garantia', 100),
            'detalle' => $body['detalle'] ?? ($body['items'] ?? null),
        ];

        $created = $this->service->createWithDetalle($payload);
        return Response::json(['success' => true, 'data' => $created], 201);
    }

    public function update(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }

        $body = Validator::requireArray($request->body);

        $payload = [
            'cliente_id' => Validator::optionalInt($body, 'cliente_id'),
            'contacto_id' => Validator::optionalInt($body, 'contacto_id'),
            'fecha_emision' => Validator::requiredString($body, 'fecha_emision', 8, 10),
            'moneda' => Validator::optionalString($body, 'moneda', 10),
            'forma_pago' => Validator::optionalString($body, 'forma_pago', 100),
            'plazo_entrega' => Validator::optionalString($body, 'plazo_entrega', 100),
            'garantia' => Validator::optionalString($body, 'garantia', 100),
            'detalle' => $body['detalle'] ?? ($body['items'] ?? null),
        ];

        $updated = $this->service->updateWithDetalle($id, $payload);
        return Response::json(['success' => true, 'data' => $updated], 200);
    }

    public function delete(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }
        $existing = $this->cotizaciones->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Cotización no encontrada');
        }

        $this->cotizaciones->delete($id);
        return Response::json(['success' => true, 'data' => ['deleted' => true]], 200);
    }
}

