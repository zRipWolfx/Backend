<?php

declare(strict_types=1);

namespace App\Models;

final class Cotizacion
{
    public function __construct(
        public int $id,
        public string $numero,
        public ?int $clienteId,
        public ?int $contactoId,
        public ?string $fechaEmision,
        public ?string $moneda,
        public float $subtotal,
        public float $igv,
        public float $total
    ) {
    }
}

