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

-- サンプルユーザー
INSERT INTO users (username, email, password_hash)
VALUES ('sampleuser', 'sample@example.com', '$2y$10$ZlABBJEaL6y2xIi79bpHEeHsSRR2b3zZpmI7IIVDuEN9w0Tk/ENXK');

-- インデックス
CREATE INDEX idx_attendance_user_date ON attendance(user_id, date);