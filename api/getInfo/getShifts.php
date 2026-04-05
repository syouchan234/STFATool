<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/resp.php';
require_once __DIR__ . '/../common/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('メソッドが許可されていません', 405);
}

$userId = Auth::requireAuth();

$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$staffId = isset($_GET['staffId']) ? (int)$_GET['staffId'] : null;

if ($startDate === '' || $endDate === '') {
    Response::error('startDateとendDateが必須です', 400);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    Response::error('日付形式はYYYY-MM-DDです', 400);
}

if ($startDate > $endDate) {
    Response::error('startDateはendDateより前である必要があります', 400);
}

// 参照可能期間チェック: 当月から前後2ヶ月
$currentMonth = date('Y-m-01');
$minDate = date('Y-m-d', strtotime($currentMonth . ' -2 months'));
$maxDate = date('Y-m-d', strtotime($currentMonth . ' +2 months +1 month -1 day'));

if ($startDate < $minDate || $endDate > $maxDate) {
    Response::error('参照可能期間は当月から前後2ヶ月です', 400);
}

$pdo = Database::getConnection();

// 権限チェック: 一般ユーザーは自分のシフトのみ
$stmt = $pdo->prepare('SELECT role_id FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = $user && $user['role_id'] == 3;

$query = 'SELECT id, staff_id as staffId, date, start_time as startTime, end_time as endTime, memo as note FROM shifts WHERE date BETWEEN ? AND ?';
$params = [$startDate, $endDate];

if (!$isAdmin) {
    $query .= ' AND staff_id = ?';
    $params[] = $userId;
} elseif ($staffId !== null) {
    $query .= ' AND staff_id = ?';
    $params[] = $staffId;
}

$query .= ' ORDER BY date, start_time';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

Response::success($shifts);