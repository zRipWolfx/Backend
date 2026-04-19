<?php

declare(strict_types=1);

namespace App\Repositories;

final class ProductoRepository extends BaseRepository
{
    public function list(): array
    {
        $stmt = $this->pdo()->query('SELECT * FROM productos ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM productos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO productos (nombre, descripcion, precio, unidad_medida)
             VALUES (:nombre, :descripcion, :precio, :unidad_medida)'
        );
        $stmt->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'] ?? 0,
            'unidad_medida' => $data['unidad_medida'] ?? null,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE productos
             SET nombre = :nombre,
                 descripcion = :descripcion,
                 precio = :precio,
                 unidad_medida = :unidad_medida
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'] ?? 0,
            'unidad_medida' => $data['unidad_medida'] ?? null,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM productos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

