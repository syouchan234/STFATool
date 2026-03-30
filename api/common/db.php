<?php
declare(strict_types=1);

/**
 * データベース接続の共通処理
 */

class Database {
    private static ?PDO $pdo = null;

    /**
     * データベース接続を取得する
     * @return PDO データベース接続オブジェクト
     * @throws PDOException 接続失敗時
     */
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            // データベース設定（将来的には環境変数から取得）
            $host = 'localhost';
            $dbname = 'staft_db'; // 仮のデータベース名
            $username = 'root';
            $password = '';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            self::$pdo = new PDO($dsn, $username, $password, $options);
        }

        return self::$pdo;
    }
}