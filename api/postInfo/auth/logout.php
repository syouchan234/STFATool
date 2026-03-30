<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../api/common/resp.php';
require_once __DIR__ . '/../../../api/common/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('メソッドが許可されていません', 405);
}

Auth::requireAuth();
Auth::logout();

Response::success(['message' => 'ログアウトしました']);
