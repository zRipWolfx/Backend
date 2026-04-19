<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Exceptions\HttpException;
use App\Core\Jwt;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    public function register(string $correo, string $password): array
    {
        if (strlen($password) < 6) {
            throw new HttpException(422, 'Validación fallida', ['errors' => ['password' => ['Mínimo 6 caracteres']]]);
        }

        $existing = $this->users->findByEmail($correo);
        if ($existing !== null) {
            throw new HttpException(409, 'El correo ya está registrado');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        if (!is_string($hash) || $hash === '') {
            throw new HttpException(500, 'No se pudo procesar la contraseña');
        }

        $id = $this->users->create($correo, $hash);

        return $this->issueToken($id, $correo);
    }

    public function login(string $correo, string $password): array
    {
        $user = $this->users->findByEmail($correo);
        if ($user === null || (int)($user['estado'] ?? 1) !== 1) {
            throw new HttpException(401, 'Credenciales inválidas');
        }

        $hash = (string)($user['password'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            throw new HttpException(401, 'Credenciales inválidas');
        }

        return $this->issueToken((int)$user['id'], (string)$user['correo']);
    }

    private function issueToken(int $userId, string $correo): array
    {
        $secret = Env::get('JWT_SECRET', '');
        if ($secret === '') {
            throw new HttpException(500, 'JWT_SECRET no configurado');
        }
        $ttl = (int)(Env::get('JWT_TTL_SECONDS', '86400') ?? '86400');
        if ($ttl <= 0) {
            $ttl = 86400;
        }

        $token = Jwt::encode(['sub' => $userId, 'correo' => $correo], $secret, $ttl);
        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'user' => ['id' => $userId, 'correo' => $correo],
        ];
    }
}

