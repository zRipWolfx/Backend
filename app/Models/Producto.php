<?php

declare(strict_types=1);

namespace App\Models;

final class Producto
{
    public function __construct(
        public int $id,
        public string $nombre,
        public ?string $descripcion,
        public float $precio,
        public ?string $unidadMedida
    ) {
    }
}

