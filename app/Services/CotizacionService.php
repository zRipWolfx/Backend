<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Exceptions\HttpException;
use App\Repositories\ConfiguracionRepository;
use App\Repositories\CotizacionRepository;
use App\Repositories\DetalleCotizacionRepository;
use App\Repositories\ProductoRepository;

final class CotizacionService
{
    public function __construct(
        private CotizacionRepository $cotizaciones,
        private DetalleCotizacionRepository $detalles,
        private ProductoRepository $productos,
        private ConfiguracionRepository $configuracion
    ) {
    }

    public function getWithDetalle(int $id): array
    {
        $cot = $this->cotizaciones->findById($id);
        if ($cot === null) {
            throw new HttpException(404, 'Cotización no encontrada');
        }
        $items = $this->detalles->listByCotizacionId($id);
        $cot['detalle'] = $items;
        return $cot;
    }

    public function createWithDetalle(array $data): array
    {
        $items = $data['detalle'] ?? $data['items'] ?? null;
        if (!is_array($items) || count($items) === 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ['Debe enviar al menos un ítem']]]);
        }

        $cfg = $this->configuracion->createDefaultIfMissing();
        $igvPct = (float)($cfg['igv'] ?? 18.0);

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $computed = $this->computeTotals($items, $igvPct);

            $cotizacionId = $this->cotizaciones->create(array_merge($data, $computed));

            $numero = $this->formatNumero($cotizacionId);
            $this->cotizaciones->updateNumero($cotizacionId, $numero);

            foreach ($computed['items_normalized'] as $item) {
                $this->detalles->create([
                    'cotizacion_id' => $cotizacionId,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'total' => $item['total'],
                    'descripcion' => $item['descripcion'],
                ]);
            }

            $pdo->commit();

            return $this->getWithDetalle($cotizacionId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateWithDetalle(int $id, array $data): array
    {
        $existing = $this->cotizaciones->findById($id);
        if ($existing === null) {
            throw new HttpException(404, 'Cotización no encontrada');
        }

        $items = $data['detalle'] ?? $data['items'] ?? null;
        if (!is_array($items) || count($items) === 0) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ['Debe enviar al menos un ítem']]]);
        }

        $cfg = $this->configuracion->createDefaultIfMissing();
        $igvPct = (float)($cfg['igv'] ?? 18.0);

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $computed = $this->computeTotals($items, $igvPct);

            $this->cotizaciones->update($id, array_merge($data, $computed));

            $this->detalles->deleteByCotizacionId($id);
            foreach ($computed['items_normalized'] as $item) {
                $this->detalles->create([
                    'cotizacion_id' => $id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'total' => $item['total'],
                    'descripcion' => $item['descripcion'],
                ]);
            }

            $pdo->commit();
            return $this->getWithDetalle($id);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function computeTotals(array $items, float $igvPct): array
    {
        $normalized = [];
        $subtotal = 0.0;

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ["Ítem #{$idx} inválido"]]]);
            }

            $cantidad = $item['cantidad'] ?? null;
            if (!is_numeric($cantidad) || (float)$cantidad <= 0) {
                throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ["Ítem #{$idx}: cantidad inválida"]]]);
            }
            $cantidad = (float)$cantidad;

            $productoId = $item['producto_id'] ?? null;
            $descripcion = $item['descripcion'] ?? null;
            if ($productoId === null && (!is_string($descripcion) || trim($descripcion) === '')) {
                throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ["Ítem #{$idx}: producto_id o descripcion requerido"]]]);
            }

            if ($productoId !== null && !(is_int($productoId) || (is_string($productoId) && ctype_digit($productoId)))) {
                throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ["Ítem #{$idx}: producto_id inválido"]]]);
            }
            $productoId = $productoId !== null ? (int)$productoId : null;

            $precioUnitario = $item['precio_unitario'] ?? null;
            if ($precioUnitario === null && $productoId !== null) {
                $prod = $this->productos->findById($productoId);
                if ($prod === null) {
                    throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ["Ítem #{$idx}: producto no existe"]]]);
                }
                $precioUnitario = $prod['precio'] ?? 0;
            }
            if (!is_numeric($precioUnitario)) {
                throw new HttpException(422, 'Validación fallida', ['errors' => ['detalle' => ["Ítem #{$idx}: precio_unitario inválido"]]]);
            }
            $precioUnitario = (float)$precioUnitario;

            $lineTotal = round($cantidad * $precioUnitario, 2);
            $subtotal += $lineTotal;

            $normalized[] = [
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'total' => $lineTotal,
                'descripcion' => is_string($descripcion) ? trim($descripcion) : null,
            ];
        }

        $subtotal = round($subtotal, 2);
        $igv = round($subtotal * ($igvPct / 100.0), 2);
        $total = round($subtotal + $igv, 2);

        return [
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total,
            'items_normalized' => $normalized,
        ];
    }

    private function formatNumero(int $id): string
    {
        return 'COT-' . date('Y') . '-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
    }
}

