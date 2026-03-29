# DB設計

- `users`
  - id PK
  - name VARCHAR
  - email UNIQUE
  - password_hash
  - role ENUM('admin','staff')
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

- `requests`
  - id PK
  - staff_id
  - date
  - request_type ENUM('holiday','off','work')
  - status ENUM('pending','approved','rejected')
  - manager_checked TINYINT (0・1)
  - manager_id NULLABLE FK->users(id)
  - manager_note TEXT
  - comment TEXT
  - created_at, updated_at