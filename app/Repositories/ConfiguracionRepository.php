<?php

declare(strict_types=1);

namespace App\Repositories;

final class ConfiguracionRepository extends BaseRepository
{
    public function getFirst(): ?array
    {
        $stmt = $this->pdo()->query('SELECT * FROM configuracion ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function createDefaultIfMissing(): array
    {
        $existing = $this->getFirst();
        if (is_array($existing)) {
            return $existing;
        }

        $stmt = $this->pdo()->prepare(
            'INSERT INTO configuracion (color_primario, color_secundario, moneda, igv)
             VALUES (:color_primario, :color_secundario, :moneda, :igv)'
        );
        $stmt->execute([
            'color_primario' => '#1f2a7a',
            'color_secundario' => '#ffffff',
            'moneda' => 'S/',
            'igv' => 18.00,
        ]);

        $created = $this->getFirst();
        return $created ?? [];
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE configuracion
             SET color_primario = :color_primario,
                 color_secundario = :color_secundario,
                 moneda = :moneda,
                 igv = :igv
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'color_primario' => $data['color_primario'],
            'color_secundario' => $data['color_secundario'],
            'moneda' => $data['moneda'],
            'igv' => $data['igv'],
        ]);
    }
}

