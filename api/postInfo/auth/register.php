<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../api/common/db.php';
require_once __DIR__ . '/../../../api/common/resp.php';

// POSTのみ対応
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('メソッドが許可されていません', 405);
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!is_array($data)) {
    Response::error('JSONパースエラー', 400);
}

$name = trim((string)($data['name'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
    Response::error('name, email, passwordは必須です', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error('正しいメールアドレスを指定してください', 400);
}

$pdo = Database::getConnection();

try {
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role_id) VALUES (?, ?, ?, 1)');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt->execute([$name, $email, $hash]);

    $userId = (int)$pdo->lastInsertId();
    Response::success(['id' => $userId, 'name' => $name, 'email' => $email], 201);
} catch (PDOException $e) {
    if ($e->errorInfo[1] === 1062) {
        Response::error('名前またはメールアドレスが既に存在します', 409);
    }

    Response::error('データベースエラー: ' . $e->getMessage(), 500);
}
