<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ClienteRepository;

final class ClientesController
{
    private ClienteRepository $clientes;

    public function __construct()
    {
        $this->clientes = new ClienteRepository();
    }

    public function index(Request $request): Response
    {
        $rows = $this->clientes->list();
        return Response::json(['success' => true, 'data' => $rows], 200);
    }

    public function show(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }
        $row = $this->clientes->findById($id);
        if ($row === null) {
            throw new HttpException(404, 'Cliente no encontrado');
        }
        return Response::json(['success' => true, 'data' => $row], 200);
    }

    public function create(Request $request): Response
    {
        $body = Validator::requireArray($request->body);

        $data = [
            'tipo_documento' => Validator::requiredEnum($body, 'tipo_documento', ['DNI', 'RUC']),
            'numero_documento' => Validator::requiredString($body, 'numero_documento', 1, 20),
            'nombre' => Validator::optionalString($body, 'nombre', 150),
            'direccion' => Validator::optionalString($body, 'direccion', 200),
            'telefono' => Validator::optionalString($body, 'telefono', 20),
            'email' => ($body['email'] ?? null) ? Validator::requiredEmail(['email' => $body['email']], 'email') : null,
        ];

        $id = $this->clientes->create($data);
        $created = $this->clientes->findById($id);
        return Response::json(['success' => true, 'data' => $created], 201);
    }

    public function update(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }
        $existing = $this->clientes->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Cliente no encontrado');
        }

        $body = Validator::requireArray($request->body);

        $data = [
            'tipo_documento' => Validator::requiredEnum($body, 'tipo_documento', ['DNI', 'RUC']),
            'numero_documento' => Validator::requiredString($body, 'numero_documento', 1, 20),
            'nombre' => Validator::optionalString($body, 'nombre', 150),
            'direccion' => Validator::optionalString($body, 'direccion', 200),
            'telefono' => Validator::optionalString($body, 'telefono', 20),
            'email' => ($body['email'] ?? null) ? Validator::requiredEmail(['email' => $body['email']], 'email') : null,
        ];

        $this->clientes->update($id, $data);
        $updated = $this->clientes->findById($id);
        return Response::json(['success' => true, 'data' => $updated], 200);
    }

    public function delete(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }
        $existing = $this->clientes->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Cliente no encontrado');
        }
        $this->clientes->delete($id);
        return Response::json(['success' => true, 'data' => ['deleted' => true]], 200);
    }
}

