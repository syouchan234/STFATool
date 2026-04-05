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
            // データベース設定（Docker環境用）
            $host = 'db';
            $dbname = 'staft_db';
            $username = 'staft_user';
            $password = 'staft_password';

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