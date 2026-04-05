<?php
/**
 * ログイン処理（メール or ユーザーID + パスワード認証）
 */
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

$email = trim((string)($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($email === '' || $password === '') {
    Response::error('emailとpasswordが必須です', 400);
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT u.id, u.name, u.email, u.password_hash, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.is_active = 1 LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, (string)$user['password_hash'])) {
    Response::error('認証に失敗しました', 401);
}

Auth::login((int)$user['id']);

Response::success([
    'user' => [
        'id' => (int)$user['id'],
        'name' => (string)$user['name'],
        'role' => (string)$user['role'],
    ]
]);
