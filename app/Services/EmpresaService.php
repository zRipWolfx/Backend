<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Exceptions\HttpException;

final class EmpresaService
{
    public function storeLogo(array $file, string $publicPath): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['logo' => ['Archivo inválido']]]);
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_file($tmpName)) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['logo' => ['Archivo inválido']]]);
        }

        $info = @getimagesize($tmpName);
        if ($info === false) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['logo' => ['Debe ser una imagen']]]);
        }

        $mime = (string)($info['mime'] ?? '');
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
        if ($ext === null) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['logo' => ['Formato permitido: jpg, png, webp']]]);
        }

        $targetDir = rtrim($publicPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'logos';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new HttpException(500, 'No se pudo crear el directorio de subida');
        }

        $random = bin2hex(random_bytes(8));
        $filename = 'logo_' . date('Ymd_His') . '_' . $random . '.' . $ext;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new HttpException(500, 'No se pudo guardar el archivo');
        }

        return '/uploads/logos/' . $filename;
    }
}

