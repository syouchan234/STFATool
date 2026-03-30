-- 初期データベース設定
-- 仮のテーブル構造（DB設計.mdに基づいて後で更新）

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    check_in DATETIME,
    check_out DATETIME,
    date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Why: 初期ユーザーセット。本番環境ではこれらのアカウントは削除し、管理者アカウントで初期化する必要がある
-- サンプルユーザー (ID: sampleuser / PASSWORD: Password123!)
INSERT INTO users (username, email, password_hash)
VALUES ('sampleuser', 'sample@example.com', '$2y$10$ZlABBJEaL6y2xIi79bpHEeHsSRR2b3zZpmI7IIVDuEN9w0Tk/ENXK');

-- テスト用アカウント (ID: testuser / PASSWORD: Test123!)
INSERT INTO users (username, email, password_hash)
VALUES ('testuser', 'test@example.com', '$2y$10$Uyl1MhdYklru6yxVhbfc8.Uq0n0WfKHyDt1WIQyCY.qttHpqxQzGa');

-- 開発者用アカウント (ID: devuser / PASSWORD: Dev123!)
INSERT INTO users (username, email, password_hash)
VALUES ('devuser', 'dev@example.com', '$2y$10$Z3ujXGMvsWeGj7ec0P9O/.fTDYmgAsIgi.5GH3zeYYuyaRnGAW58u');

-- 管理者用アカウント (ID: admin / PASSWORD: Admin123!)
INSERT INTO users (username, email, password_hash)
VALUES ('admin', 'admin@example.com', '$2y$10$ilfFvL0RKTgkHUtWkxi2Q.YhTyr8R.9yZL9jx/Afp7B3mpCojShbO');

-- インデックス
-- Why: ユーザーIDでの検索が頻繁なため、ユーザー認証速度向上のインデックスを追加
CREATE INDEX idx_user_username ON users(username);
CREATE INDEX idx_user_email ON users(email);

-- Why: 勤怠データは日付・ユーザーIDでのクエリが支配的なため複合インデックスを設定
CREATE INDEX idx_attendance_user_date ON attendance(user_id, date);