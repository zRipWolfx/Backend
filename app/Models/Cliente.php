<?php

declare(strict_types=1);

namespace App\Models;

final class Cliente
{
    public function __construct(
        public int $id,
        public string $tipoDocumento,
        public string $numeroDocumento,
        public ?string $nombre,
        public ?string $direccion,
        public ?string $telefono,
        public ?string $email
    ) {
    }
}

