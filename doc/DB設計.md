# DB設計

- `roles`
  - id PK
  - name VARCHAR(50) UNIQUE (例: '一般', '責任者', '管理者')
  - created_at TIMESTAMP

- `users`
  - id PK
  - name VARCHAR
  - email UNIQUE
  - password_hash
  - role_id FK->roles(id)
  - is_active TINYINT
  - created_at, updated_at

- `shifts`
  - id PK
  - staff_id FK->users(id)
  - date DATE
  - start_time TIME
  - end_time TIME
  - memo TEXT
  - manager_checked TINYINT (0・1)
  - manager_id NULLABLE FK->users(id)
  - created_by
  - updated_by
  - created_at, updated_at
  - UNIQUE(staff_id, date, start_time, end_time)