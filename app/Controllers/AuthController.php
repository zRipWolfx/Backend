<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Services\AuthService;
use App\Repositories\UserRepository;

final class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService(new UserRepository());
    }

    public function register(Request $request): Response
    {
        $body = Validator::requireArray($request->body);
        $correo = Validator::requiredEmail($body, 'correo');
        $password = Validator::requiredString($body, 'password', 6, 255);

        $data = $this->auth->register($correo, $password);
        return Response::json(['success' => true, 'data' => $data], 201);
    }

    public function login(Request $request): Response
    {
        $body = Validator::requireArray($request->body);
        $correo = Validator::requiredEmail($body, 'correo');
        $password = Validator::requiredString($body, 'password', 1, 255);

        $data = $this->auth->login($correo, $password);
        return Response::json(['success' => true, 'data' => $data], 200);
    }
}

