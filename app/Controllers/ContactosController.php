<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\EmpresaContactosRepository;

final class ContactosController
{
    private EmpresaContactosRepository $contactos;

    public function __construct()
    {
        $this->contactos = new EmpresaContactosRepository();
    }

    public function index(Request $request): Response
    {
        $rows = $this->contactos->list();
        return Response::json(['success' => true, 'data' => $rows], 200);
    }

    public function create(Request $request): Response
    {
        $body = Validator::requireArray($request->body);

        $data = [
            'empresa_id' => Validator::requiredInt($body, 'empresa_id'),
            'nombre' => Validator::requiredString($body, 'nombre', 1, 150),
            'cargo' => Validator::optionalString($body, 'cargo', 100),
            'telefono' => Validator::optionalString($body, 'telefono', 20),
            'email' => ($body['email'] ?? null) ? Validator::requiredEmail(['email' => $body['email']], 'email') : null,
            'direccion' => Validator::optionalString($body, 'direccion', 200),
            'estado' => Validator::optionalInt($body, 'estado') ?? 1,
        ];

        $id = $this->contactos->create($data);
        $created = $this->contactos->findById($id);
        return Response::json(['success' => true, 'data' => $created], 201);
    }

    public function update(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }

        $existing = $this->contactos->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Contacto no encontrado');
        }

        $body = Validator::requireArray($request->body);

        $data = [
            'nombre' => Validator::requiredString($body, 'nombre', 1, 150),
            'cargo' => Validator::optionalString($body, 'cargo', 100),
            'telefono' => Validator::optionalString($body, 'telefono', 20),
            'email' => ($body['email'] ?? null) ? Validator::requiredEmail(['email' => $body['email']], 'email') : null,
            'direccion' => Validator::optionalString($body, 'direccion', 200),
            'estado' => Validator::optionalInt($body, 'estado') ?? (int)($existing['estado'] ?? 1),
        ];

        $this->contactos->update($id, $data);
        $updated = $this->contactos->findById($id);
        return Response::json(['success' => true, 'data' => $updated], 200);
    }

    public function delete(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }

        $existing = $this->contactos->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Contacto no encontrado');
        }

        $this->contactos->delete($id);
        return Response::json(['success' => true, 'data' => ['deleted' => true]], 200);
    }
}

