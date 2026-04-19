<?php

declare(strict_types=1);

namespace App\Repositories;

final class CotizacionRepository extends BaseRepository
{
    public function list(): array
    {
        $stmt = $this->pdo()->query(
            'SELECT c.*, cl.nombre AS cliente_nombre, cl.numero_documento AS cliente_documento
             FROM cotizaciones c
             LEFT JOIN clientes cl ON cl.id = c.cliente_id
             ORDER BY c.id DESC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM cotizaciones WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO cotizaciones
            (numero, cliente_id, contacto_id, fecha_emision, moneda, forma_pago, plazo_entrega, garantia, subtotal, igv, total)
            VALUES
            (:numero, :cliente_id, :contacto_id, :fecha_emision, :moneda, :forma_pago, :plazo_entrega, :garantia, :subtotal, :igv, :total)'
        );
        $stmt->execute([
            'numero' => $data['numero'] ?? null,
            'cliente_id' => $data['cliente_id'] ?? null,
            'contacto_id' => $data['contacto_id'] ?? null,
            'fecha_emision' => $data['fecha_emision'] ?? null,
            'moneda' => $data['moneda'] ?? null,
            'forma_pago' => $data['forma_pago'] ?? null,
            'plazo_entrega' => $data['plazo_entrega'] ?? null,
            'garantia' => $data['garantia'] ?? null,
            'subtotal' => $data['subtotal'] ?? null,
            'igv' => $data['igv'] ?? null,
            'total' => $data['total'] ?? null,
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE cotizaciones
             SET cliente_id = :cliente_id,
                 contacto_id = :contacto_id,
                 fecha_emision = :fecha_emision,
                 moneda = :moneda,
                 forma_pago = :forma_pago,
                 plazo_entrega = :plazo_entrega,
                 garantia = :garantia,
                 subtotal = :subtotal,
                 igv = :igv,
                 total = :total
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'cliente_id' => $data['cliente_id'] ?? null,
            'contacto_id' => $data['contacto_id'] ?? null,
            'fecha_emision' => $data['fecha_emision'] ?? null,
            'moneda' => $data['moneda'] ?? null,
            'forma_pago' => $data['forma_pago'] ?? null,
            'plazo_entrega' => $data['plazo_entrega'] ?? null,
            'garantia' => $data['garantia'] ?? null,
            'subtotal' => $data['subtotal'] ?? null,
            'igv' => $data['igv'] ?? null,
            'total' => $data['total'] ?? null,
        ]);
    }

    public function updateNumero(int $id, string $numero): void
    {
        $stmt = $this->pdo()->prepare('UPDATE cotizaciones SET numero = :numero WHERE id = :id');
        $stmt->execute(['id' => $id, 'numero' => $numero]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM cotizaciones WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

