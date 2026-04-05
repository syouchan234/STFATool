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

$id = isset($data['id']) ? (int)$data['id'] : null;
$staffId = (int)($data['staffId'] ?? 0);
$date = trim((string)($data['date'] ?? ''));
$startTime = trim((string)($data['startTime'] ?? ''));
$endTime = trim((string)($data['endTime'] ?? ''));
$memo = trim((string)($data['memo'] ?? ''));

if ($staffId === 0 || $date === '' || $startTime === '' || $endTime === '') {
    Response::error('staffId, date, startTime, endTimeは必須です', 400);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    Response::error('date形式はYYYY-MM-DDです', 400);
}

if (!preg_match('/^\d{2}:\d{2}$/', $startTime) || !preg_match('/^\d{2}:\d{2}$/', $endTime)) {
    Response::error('time形式はHH:MMです', 400);
}

if ($startTime >= $endTime) {
    Response::error('startTimeはendTimeより前である必要があります', 400);
}

$pdo = Database::getConnection();

// 権限チェック: 一般ユーザーは自分のみ、責任者/管理者は全員
$stmt = $pdo->prepare('SELECT role_id FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$isAdminOrLeader = $user && in_array($user['role_id'], [2, 3]);

if (!$isAdminOrLeader && $staffId != $userId) {
    Response::error('自分のシフトのみ編集可能です', 403);
}

// 重複チェック: 同じスタッフ、日付、時間帯で重複しない
$overlapQuery = 'SELECT id FROM shifts WHERE staff_id = ? AND date = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (start_time >= ? AND end_time <= ?))';
$overlapParams = [$staffId, $date, $endTime, $startTime, $startTime, $endTime, $startTime, $endTime];
if ($id !== null) {
    $overlapQuery .= ' AND id != ?';
    $overlapParams[] = $id;
}
$overlapStmt = $pdo->prepare($overlapQuery);
$overlapStmt->execute($overlapParams);
if ($overlapStmt->fetch()) {
    Response::error('指定時間帯に重複するシフトが存在します', 400);
}

try {
    if ($id === null) {
        // 新規作成
        $stmt = $pdo->prepare('INSERT INTO shifts (staff_id, date, start_time, end_time, memo, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$staffId, $date, $startTime, $endTime, $memo, $userId, $userId]);
        $newId = (int)$pdo->lastInsertId();
        Response::success(['id' => $newId]);
    } else {
        // 更新
        $stmt = $pdo->prepare('UPDATE shifts SET staff_id = ?, date = ?, start_time = ?, end_time = ?, memo = ?, updated_by = ? WHERE id = ?');
        $stmt->execute([$staffId, $date, $startTime, $endTime, $memo, $userId, $id]);
        Response::success(['id' => $id]);
    }
} catch (PDOException $e) {
    Response::error('データベースエラー: ' . $e->getMessage(), 500);
}