<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\EmpresaRepository;
use App\Services\EmpresaService;

final class EmpresaController
{
    private EmpresaRepository $empresas;
    private EmpresaService $service;

    public function __construct()
    {
        $this->empresas = new EmpresaRepository();
        $this->service = new EmpresaService();
    }

    public function get(Request $request): Response
    {
        $empresa = $this->empresas->getFirst();
        return Response::json(['success' => true, 'data' => $empresa], 200);
    }

    public function create(Request $request): Response
    {
        $body = is_array($request->body) ? $request->body : [];

        $data = [
            'nombre' => Validator::optionalString($body, 'nombre', 150),
            'razon_social' => Validator::optionalString($body, 'razon_social', 200),
            'ruc' => Validator::optionalString($body, 'ruc', 20),
            'direccion' => Validator::optionalString($body, 'direccion', 200),
            'telefono' => Validator::optionalString($body, 'telefono', 20),
            'email' => ($body['email'] ?? null) ? Validator::requiredEmail(['email' => $body['email']], 'email') : null,
            'logo' => null,
        ];

        $logoFile = $request->files['logo'] ?? null;
        if (is_array($logoFile) && ($logoFile['tmp_name'] ?? '') !== '') {
            $publicPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
            $data['logo'] = $this->service->storeLogo($logoFile, $publicPath);
        }

        $id = $this->empresas->create($data);
        $created = $this->empresas->findById($id);

        return Response::json(['success' => true, 'data' => $created], 201);
    }

    public function update(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }

        $existing = $this->empresas->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Empresa no encontrada');
        }

        $body = is_array($request->body) ? $request->body : [];

        $data = [
            'nombre' => Validator::optionalString($body, 'nombre', 150) ?? $existing['nombre'],
            'razon_social' => Validator::optionalString($body, 'razon_social', 200) ?? $existing['razon_social'],
            'ruc' => Validator::optionalString($body, 'ruc', 20) ?? $existing['ruc'],
            'direccion' => Validator::optionalString($body, 'direccion', 200) ?? $existing['direccion'],
            'telefono' => Validator::optionalString($body, 'telefono', 20) ?? $existing['telefono'],
            'email' => ($body['email'] ?? null) ? Validator::requiredEmail(['email' => $body['email']], 'email') : ($existing['email'] ?? null),
            'logo' => $existing['logo'] ?? null,
        ];

        $logoFile = $request->files['logo'] ?? null;
        if (is_array($logoFile) && ($logoFile['tmp_name'] ?? '') !== '') {
            $publicPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
            $data['logo'] = $this->service->storeLogo($logoFile, $publicPath);
        }

        $this->empresas->update($id, $data);
        $updated = $this->empresas->findById($id);

        return Response::json(['success' => true, 'data' => $updated], 200);
    }
}

