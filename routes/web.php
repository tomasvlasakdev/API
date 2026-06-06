<?php

use App\Controllers\UiController;
use App\Controllers\AuthController;

/** @var \App\Core\Router $router */

$router->get('/', [UiController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/logs', [UiController::class, 'logs']);
$router->get('/users', [UiController::class, 'users']);
$router->post('/users', [UiController::class, 'users']);
$router->get('/privacy-policy', [UiController::class, 'privacyPolicy']);
$router->get('/refresh', [UiController::class, 'refresh']);
$router->get('/api-keys', [UiController::class, 'apiKeys']);
$router->post('/api-keys', [UiController::class, 'apiKeys']);
