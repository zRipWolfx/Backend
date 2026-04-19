<?php

declare(strict_types=1);

namespace App\Repositories;

final class EmpresaContactosRepository extends BaseRepository
{
    public function list(): array
    {
        $stmt = $this->pdo()->query('SELECT * FROM empresa_contactos ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM empresa_contactos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO empresa_contactos (empresa_id, nombre, cargo, telefono, email, direccion, estado)
             VALUES (:empresa_id, :nombre, :cargo, :telefono, :email, :direccion, :estado)'
        );
        $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'nombre' => $data['nombre'],
            'cargo' => $data['cargo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'estado' => $data['estado'] ?? 1,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE empresa_contactos
             SET nombre = :nombre,
                 cargo = :cargo,
                 telefono = :telefono,
                 email = :email,
                 direccion = :direccion,
                 estado = :estado
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'cargo' => $data['cargo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'estado' => $data['estado'] ?? 1,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM empresa_contactos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

