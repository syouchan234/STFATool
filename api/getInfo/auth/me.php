<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../api/common/db.php';
require_once __DIR__ . '/../../../api/common/resp.php';
require_once __DIR__ . '/../../../api/common/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('メソッドが許可されていません', 405);
}

$pdo = Database::getConnection();
$userId = Auth::requireAuth();
$user = Auth::getCurrentUser($pdo);

if ($user === null) {
    Response::error('ユーザーが見つかりません', 404);
}

Response::success($user);
