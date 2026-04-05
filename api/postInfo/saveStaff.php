<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/resp.php';
require_once __DIR__ . '/../common/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('メソッドが許可されていません', 405);
}

$userId = Auth::requireAuth();

// 管理者権限チェック
$pdo = Database::getConnection();
$stmt = $pdo->prepare('SELECT role_id FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['role_id'] != 3) {
    Response::error('管理者権限が必要です', 403);
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!is_array($data)) {
    Response::error('JSONパースエラー', 400);
}

$id = isset($data['id']) ? (int)$data['id'] : null;
$name = trim((string)($data['name'] ?? ''));
$role = trim((string)($data['role'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$password = isset($data['password']) ? (string)$data['password'] : null;

if ($name === '' || $role === '' || $email === '') {
    Response::error('name, role, emailは必須です', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error('正しいメールアドレスを指定してください', 400);
}

// role_id取得
$roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
$roleStmt->execute([$role]);
$roleData = $roleStmt->fetch(PDO::FETCH_ASSOC);
if (!$roleData) {
    Response::error('無効なロールです', 400);
}
$roleId = (int)$roleData['id'];

try {
    if ($id === null) {
        // 新規作成
        if ($password === null || $password === '') {
            Response::error('新規作成時はpasswordが必須です', 400);
        }
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role_id, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?)');
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$name, $email, $hash, $roleId, $userId, $userId]);
        $newId = (int)$pdo->lastInsertId();
        Response::success(['id' => $newId]);
    } else {
        // 更新
        $query = 'UPDATE users SET name = ?, email = ?, role_id = ?, updated_by = ?';
        $params = [$name, $email, $roleId, $userId];
        if ($password !== null && $password !== '') {
            $query .= ', password_hash = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $query .= ' WHERE id = ?';
        $params[] = $id;
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        Response::success(['id' => $id]);
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] === 1062) {
        Response::error('メールアドレスが既に存在します', 409);
    }
    Response::error('データベースエラー: ' . $e->getMessage(), 500);
}