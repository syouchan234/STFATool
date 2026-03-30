<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/resp.php';

/**
 * 認証セッション管理
 */
class Auth
{
    private const SESSION_NAME = 'STAFTSESSID';

    /**
     * セッションを初期化し、HttpOnly Cookieを設定する
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(self::SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    /**
     * ログイン情報をセッションに保存
     * @param int $userId
     */
    public static function login(int $userId): void
    {
        self::startSession();
        $_SESSION['user_id'] = $userId;
    }

    /**
     * セッションを破棄
     */
    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * 認証済みかどうかを判定
     * @return bool
     */
    public static function isAuthenticated(): bool
    {
        self::startSession();
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
    }

    /**
     * 認証済みユーザーIDを取得
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        self::startSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * 認証が必要な処理で呼び出す。未認証ならエラーを返して終了
     * @return int 認証済みユーザーID
     */
    public static function requireAuth(): int
    {
        if (!self::isAuthenticated()) {
            Response::error('認証が必要です', 401);
        }
        return self::getUserId();
    }

    /**
     * 現在の認証ユーザー情報を取得
     * @param PDO $pdo
     * @return array|null
     */
    public static function getCurrentUser(PDO $pdo): ?array
    {
        $userId = self::getUserId();
        if ($userId === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT id, username, email, created_at FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}
