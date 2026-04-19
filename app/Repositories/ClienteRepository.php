<?php

declare(strict_types=1);

namespace App\Repositories;

final class ClienteRepository extends BaseRepository
{
    public function list(): array
    {
        $stmt = $this->pdo()->query('SELECT * FROM clientes ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM clientes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO clientes (tipo_documento, numero_documento, nombre, direccion, telefono, email)
             VALUES (:tipo_documento, :numero_documento, :nombre, :direccion, :telefono, :email)'
        );
        $stmt->execute([
            'tipo_documento' => $data['tipo_documento'],
            'numero_documento' => $data['numero_documento'],
            'nombre' => $data['nombre'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE clientes
             SET tipo_documento = :tipo_documento,
                 numero_documento = :numero_documento,
                 nombre = :nombre,
                 direccion = :direccion,
                 telefono = :telefono,
                 email = :email
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'tipo_documento' => $data['tipo_documento'],
            'numero_documento' => $data['numero_documento'],
            'nombre' => $data['nombre'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

