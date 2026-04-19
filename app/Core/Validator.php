<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\ValidationException;

final class Validator
{
    public static function requireArray(mixed $data): array
    {
        if (!is_array($data)) {
            throw new ValidationException(['body' => ['Debe ser un JSON válido (objeto)']]);
        }
        return $data;
    }

    public static function requiredString(array $data, string $key, int $min = 1, int $max = 255): string
    {
        $value = $data[$key] ?? null;
        if (!is_string($value)) {
            throw new ValidationException([$key => ['Requerido']]);
        }
        $trim = trim($value);
        if ($trim === '' || strlen($trim) < $min) {
            throw new ValidationException([$key => ['Requerido']]);
        }
        if (strlen($trim) > $max) {
            throw new ValidationException([$key => ["Máximo {$max} caracteres"]]);
        }
        return $trim;
    }

    public static function optionalString(array $data, string $key, int $max = 255): ?string
    {
        $value = $data[$key] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new ValidationException([$key => ['Debe ser texto']]);
        }
        $trim = trim($value);
        if (strlen($trim) > $max) {
            throw new ValidationException([$key => ["Máximo {$max} caracteres"]]);
        }
        return $trim === '' ? null : $trim;
    }

    public static function requiredEmail(array $data, string $key): string
    {
        $email = self::requiredString($data, $key, 3, 100);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException([$key => ['Email inválido']]);
        }
        return $email;
    }

    public static function requiredInt(array $data, string $key): int
    {
        $value = $data[$key] ?? null;
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int)$value;
        }
        throw new ValidationException([$key => ['Debe ser un entero']]);
    }

    public static function optionalInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int)$value;
        }
        throw new ValidationException([$key => ['Debe ser un entero']]);
    }

    public static function requiredEnum(array $data, string $key, array $allowed): string
    {
        $value = self::requiredString($data, $key, 1, 50);
        if (!in_array($value, $allowed, true)) {
            throw new ValidationException([$key => ['Valor inválido']]);
        }
        return $value;
    }

    public static function requiredNumber(array $data, string $key): float
    {
        $value = $data[$key] ?? null;
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (float)$value;
        }
        throw new ValidationException([$key => ['Debe ser numérico']]);
    }
}

