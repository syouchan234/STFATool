<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/resp.php';
require_once __DIR__ . '/../common/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('メソッドが許可されていません', 405);
}

$userId = Auth::requireAuth();

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!is_array($data)) {
    Response::error('JSONパースエラー', 400);
}

$staffId = (int)($data['staffId'] ?? 0);
$date = trim((string)($data['date'] ?? ''));
$requestType = trim((string)($data['requestType'] ?? ''));
$message = trim((string)($data['message'] ?? ''));

if ($staffId === 0 || $date === '' || $requestType === '') {
    Response::error('staffId, date, requestTypeは必須です', 400);
}

if (!in_array($requestType, ['holiday', 'off', 'work'])) {
    Response::error('requestTypeはholiday, off, workのいずれかです', 400);
}

if ($staffId != $userId) {
    Response::error('自分のリクエストのみ作成可能です', 403);
}

$pdo = Database::getConnection();

try {
    $stmt = $pdo->prepare('INSERT INTO requests (staff_id, date, request_type, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([$staffId, $date, $requestType, $message]);
    $newId = (int)$pdo->lastInsertId();
    Response::success(['id' => $newId]);
} catch (PDOException $e) {
    Response::error('データベースエラー: ' . $e->getMessage(), 500);
}