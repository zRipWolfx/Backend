<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ProductoRepository;

final class ProductosController
{
    private ProductoRepository $productos;

    public function __construct()
    {
        $this->productos = new ProductoRepository();
    }

    public function index(Request $request): Response
    {
        $rows = $this->productos->list();
        return Response::json(['success' => true, 'data' => $rows], 200);
    }

    public function create(Request $request): Response
    {
        $body = Validator::requireArray($request->body);
        $data = [
            'nombre' => Validator::requiredString($body, 'nombre', 1, 150),
            'descripcion' => Validator::optionalString($body, 'descripcion', 4000),
            'precio' => Validator::requiredNumber($body, 'precio'),
            'unidad_medida' => Validator::optionalString($body, 'unidad_medida', 20),
        ];

        $id = $this->productos->create($data);
        $created = $this->productos->findById($id);
        return Response::json(['success' => true, 'data' => $created], 201);
    }

    public function update(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }
        $existing = $this->productos->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Producto no encontrado');
        }

        $body = Validator::requireArray($request->body);
        $data = [
            'nombre' => Validator::requiredString($body, 'nombre', 1, 150),
            'descripcion' => Validator::optionalString($body, 'descripcion', 4000),
            'precio' => Validator::requiredNumber($body, 'precio'),
            'unidad_medida' => Validator::optionalString($body, 'unidad_medida', 20),
        ];

        $this->productos->update($id, $data);
        $updated = $this->productos->findById($id);
        return Response::json(['success' => true, 'data' => $updated], 200);
    }

    public function delete(Request $request): Response
    {
        $id = (int)($request->params['id'] ?? 0);
        if ($id <= 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['id' => ['ID inválido']]]);
        }
        $existing = $this->productos->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Producto no encontrado');
        }
        $this->productos->delete($id);
        return Response::json(['success' => true, 'data' => ['deleted' => true]], 200);
    }
}

