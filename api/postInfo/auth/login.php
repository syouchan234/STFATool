<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../api/common/db.php';
require_once __DIR__ . '/../../../api/common/resp.php';
require_once __DIR__ . '/../../../api/common/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('メソッドが許可されていません', 405);
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!is_array($data)) {
    Response::error('JSONパースエラー', 400);
}

$mode = trim((string)($data['mode'] ?? ''));
$identifier = trim((string)($data['identifier'] ?? ''));
$password = (string)($data['password'] ?? '');

if (!in_array($mode, ['email', 'username'], true) || $identifier === '' || $password === '') {
    Response::error('mode=email|username、identifier、passwordが必須です', 400);
}

$pdo = Database::getConnection();
$field = ($mode === 'email') ? 'email' : 'username';
$stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE {$field} = ? LIMIT 1");
$stmt->execute([$identifier]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, (string)$user['password_hash'])) {
    Response::error('認証に失敗しました', 401);
}

Auth::login((int)$user['id']);

Response::success([
    'id' => (int)$user['id'],
    'username' => (string)$user['username'],
    'email' => (string)$user['email'],
]);
