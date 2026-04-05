-- 初期データベース設定
-- DB設計.mdに基づく

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (name) VALUES ('一般'), ('責任者'), ('管理者');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    memo TEXT,
    manager_checked TINYINT DEFAULT 0,
    manager_id INT NULL,
    created_by INT NOT NULL,
    updated_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES users(id),
    UNIQUE(staff_id, date, start_time, end_time)
);

-- 初期ユーザーセット
-- 管理者 (role_id=3)
INSERT INTO users (name, email, password_hash, role_id)
VALUES ('管理者', 'admin@example.com', '$2y$10$ilfFvL0RKTgkHUtWkxi2Q.YhTyr8R.9yZL9jx/Afp7B3mpCojShbO', 3);

-- 責任者 (role_id=2)
INSERT INTO users (name, email, password_hash, role_id)
VALUES ('責任者', 'leader@example.com', '$2y$10$examplehash', 2);

-- 一般 (role_id=1)
INSERT INTO users (name, email, password_hash, role_id)
VALUES ('一般ユーザー', 'staff@example.com', '$2y$10$examplehash2', 1);

-- インデックス
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_shifts_staff_date ON shifts(staff_id, date);