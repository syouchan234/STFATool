<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/resp.php';
require_once __DIR__ . '/../common/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('メソッドが許可されていません', 405);
}

$userId = Auth::requireAuth();

// 管理者権限チェック (role_id=3)
$pdo = Database::getConnection();
$stmt = $pdo->prepare('SELECT role_id FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['role_id'] != 3) {
    Response::error('管理者権限が必要です', 403);
}

$stmt = $pdo->prepare('SELECT u.id, u.name, r.name as role, u.is_active as active FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id');
$stmt->execute();
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

Response::success($staff);