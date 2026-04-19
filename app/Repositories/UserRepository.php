<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findByEmail(string $correo): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM usuarios WHERE correo = :correo LIMIT 1');
        $stmt->execute(['correo' => $correo]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(string $correo, string $passwordHash): int
    {
        $stmt = $this->pdo()->prepare('INSERT INTO usuarios (correo, password) VALUES (:correo, :password)');
        $stmt->execute(['correo' => $correo, 'password' => $passwordHash]);
        return (int)$this->pdo()->lastInsertId();
    }
}

