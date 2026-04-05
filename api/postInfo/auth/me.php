<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/db.php';
require_once __DIR__ . '/../../common/resp.php';
require_once __DIR__ . '/../../common/auth.php';

$userId = Auth::requireAuth();

$pdo = Database::getConnection();
$stmt = $pdo->prepare('SELECT u.id, u.name, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    Response::error('ユーザーが見つかりません', 404);
}

Response::success($user);