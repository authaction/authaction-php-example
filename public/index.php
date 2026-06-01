<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\JwtValidator;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

header('Content-Type: application/json');

$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $path === '/public') {
    echo json_encode(['message' => 'This is a public message!']);
    exit;
}

if ($method === 'GET' && $path === '/protected') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!str_starts_with($authHeader, 'Bearer ')) {
        http_response_code(401);
        echo json_encode(['error' => 'Missing or invalid Authorization header']);
        exit;
    }

    $token = trim(substr($authHeader, 7));

    try {
        $payload = JwtValidator::verify($token);
        echo json_encode([
            'message' => 'This is a protected message!',
            'sub'     => $payload->sub ?? null,
        ]);
    } catch (\RuntimeException $e) {
        http_response_code(401);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
