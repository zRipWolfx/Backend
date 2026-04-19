<?php

declare(strict_types=1);

namespace App\Repositories;

final class EmpresaRepository extends BaseRepository
{
    public function getFirst(): ?array
    {
        $stmt = $this->pdo()->query('SELECT * FROM empresa ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM empresa WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO empresa (nombre, razon_social, ruc, direccion, telefono, email, logo)
             VALUES (:nombre, :razon_social, :ruc, :direccion, :telefono, :email, :logo)'
        );
        $stmt->execute([
            'nombre' => $data['nombre'] ?? null,
            'razon_social' => $data['razon_social'] ?? null,
            'ruc' => $data['ruc'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'logo' => $data['logo'] ?? null,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE empresa
             SET nombre = :nombre,
                 razon_social = :razon_social,
                 ruc = :ruc,
                 direccion = :direccion,
                 telefono = :telefono,
                 email = :email,
                 logo = :logo
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'] ?? null,
            'razon_social' => $data['razon_social'] ?? null,
            'ruc' => $data['ruc'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'logo' => $data['logo'] ?? null,
        ]);
    }
}

