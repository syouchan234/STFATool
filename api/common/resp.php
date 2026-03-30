<?php
declare(strict_types=1);

/**
 * レスポンス成形の共通処理
 */

class Response {
    /**
     * 成功レスポンスをJSONで返す
     * @param mixed $data レスポンスデータ
     * @param int $statusCode HTTPステータスコード（デフォルト200）
     */
    public static function success(mixed $data, int $statusCode = 200): void {
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
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}