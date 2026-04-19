<?php

declare(strict_types=1);

namespace App\Repositories;

final class DetalleCotizacionRepository extends BaseRepository
{
    public function listByCotizacionId(int $cotizacionId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT d.*, p.nombre AS producto_nombre
             FROM detalle_cotizacion d
             LEFT JOIN productos p ON p.id = d.producto_id
             WHERE d.cotizacion_id = :id
             ORDER BY d.id ASC'
        );
        $stmt->execute(['id' => $cotizacionId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO detalle_cotizacion (cotizacion_id, producto_id, cantidad, precio_unitario, total, descripcion)
             VALUES (:cotizacion_id, :producto_id, :cantidad, :precio_unitario, :total, :descripcion)'
        );
        $stmt->execute([
            'cotizacion_id' => $data['cotizacion_id'],
            'producto_id' => $data['producto_id'] ?? null,
            'cantidad' => $data['cantidad'] ?? null,
            'precio_unitario' => $data['precio_unitario'] ?? null,
            'total' => $data['total'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function deleteByCotizacionId(int $cotizacionId): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM detalle_cotizacion WHERE cotizacion_id = :id');
        $stmt->execute(['id' => $cotizacionId]);
    }
}

