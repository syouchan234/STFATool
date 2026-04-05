<?php
declare(strict_types=1);

/**
 * レスポンス成形の共通処理
 */

$allowedOrigin = 'http://localhost:3000';
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: {$allowedOrigin}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Vary: Origin');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

class Response {
    private const ALLOWED_ORIGIN = 'http://localhost:3000';

    /**
     * CORSヘッダーをセットする
     */
    private static function setCorsHeaders(): void {
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: ' . self::ALLOWED_ORIGIN);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Vary: Origin');
        }
    }

    /**
     * 成功レスポンスをJSONで返す
     * @param mixed $data レスポンスデータ
     * @param int $statusCode HTTPステータスコード（デフォルト200）
     */
    public static function success(mixed $data, int $statusCode = 200): void {
        self::setCorsHeaders();
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    /**
     * エラーレスポンスをJSONで返す
     * @param string $message エラーメッセージ
     * @param int $statusCode HTTPステータスコード（デフォルト400）
     */
    public static function error(string $message, int $statusCode = 400): void {
        self::setCorsHeaders();
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}