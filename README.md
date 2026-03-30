# STAFT Project

勤怠給与管理システム

## 開発環境のセットアップ

このプロジェクトはDockerを使用して開発環境を構築しています。

### 前提条件
- Docker
- Docker Compose

### 起動方法
1. リポジトリをクローン
2. `docker-compose up --build` を実行
3. ブラウザで以下のURLにアクセス
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8080/api
   - Database: localhost:3306 (staft_user/staft_password)

### サービス
- **db**: MariaDB 10.5
- **php**: PHP 8.5.2 with Apache
- **react**: React with CSS

### 停止方法
`docker-compose down`

### データベース初期化
初回起動時に `docker/db/init.sql` が実行され、基本テーブルが作成されます。

### 認証API
- POST /api/postInfo/auth/register.php
  - body: { username, email, password }
- POST /api/postInfo/auth/login.php
  - body: { mode: 'email'|'username', identifier, password }
- POST /api/postInfo/auth/logout.php
- GET /api/getInfo/auth/me.php

### テスト用サンプルユーザー
- ユーザーID: sampleuser
- メール: sample@example.com
- パスワード: Password123!
