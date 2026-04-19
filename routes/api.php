<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ClientesController;
use App\Controllers\ConfiguracionController;
use App\Controllers\ContactosController;
use App\Controllers\CotizacionesController;
use App\Controllers\DocsController;
use App\Controllers\EmpresaController;
use App\Controllers\ProductosController;

$router = $app->router();

$auth = new AuthController();
$empresa = new EmpresaController();
$contactos = new ContactosController();
$clientes = new ClientesController();
$productos = new ProductosController();
$cotizaciones = new CotizacionesController();
$configuracion = new ConfiguracionController();

$router->post('/auth/register', fn($req) => $auth->register($req));
$router->post('/auth/login', fn($req) => $auth->login($req));

$router->get('/empresa', fn($req) => $empresa->get($req));
$router->post('/empresa', fn($req) => $empresa->create($req));
$router->put('/empresa/{id}', fn($req) => $empresa->update($req));

$router->get('/contactos', fn($req) => $contactos->index($req));
$router->post('/contactos', fn($req) => $contactos->create($req));
$router->put('/contactos/{id}', fn($req) => $contactos->update($req));
$router->delete('/contactos/{id}', fn($req) => $contactos->delete($req));

$router->get('/clientes', fn($req) => $clientes->index($req));
$router->get('/clientes/{id}', fn($req) => $clientes->show($req));
$router->post('/clientes', fn($req) => $clientes->create($req));
$router->put('/clientes/{id}', fn($req) => $clientes->update($req));
$router->delete('/clientes/{id}', fn($req) => $clientes->delete($req));

$router->get('/productos', fn($req) => $productos->index($req));
$router->post('/productos', fn($req) => $productos->create($req));
$router->put('/productos/{id}', fn($req) => $productos->update($req));
$router->delete('/productos/{id}', fn($req) => $productos->delete($req));

$router->get('/cotizaciones', fn($req) => $cotizaciones->index($req));
$router->get('/cotizaciones/{id}', fn($req) => $cotizaciones->show($req));
$router->post('/cotizaciones', fn($req) => $cotizaciones->create($req));
$router->put('/cotizaciones/{id}', fn($req) => $cotizaciones->update($req));
$router->delete('/cotizaciones/{id}', fn($req) => $cotizaciones->delete($req));

$router->get('/configuracion', fn($req) => $configuracion->show($req));
$router->put('/configuracion', fn($req) => $configuracion->update($req));

$docs = new DocsController($router);
$router->get('/', fn($req) => $docs->index($req));
$router->get('/docs', fn($req) => $docs->index($req));
