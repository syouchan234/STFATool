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
- **sampleuser** (ID) / sample@example.com (メール): Password123!
- **testuser** (ID) / test@example.com (メール): Test123! [テスト用]
- **devuser** (ID) / dev@example.com (メール): Dev123! [開発者用]
- **admin** (ID) / admin@example.com (メール): Admin123! [管理者用]

### API疎通テスト
Docker起動後、以下のコマンドでテストスクリプトを実行：
```bash
node testAuth.js
```

テストスクリプトは以下を実行：
1. 各ユーザーでID/メールアドレスでのログイン
2. ログイン状態確認 (GET /api/getInfo/auth/me.php)
3. ログアウト
4. ログアウト後のアクセス確認（401の検証）

---

## ローカル開発環境の構築

### Docker使用時

#### 必須環境
- Docker Desktop（Windows/Mac）または Docker Engine（Linux）
- Docker Compose 1.29以上

#### 起動手順

```bash
# リポジトリのクローン
git clone <repository-url>
cd STAFT

# コンテナをビルド・起動
docker-compose up --build -d

# ログ確認
docker-compose logs -f
```

起動に成功すると以下がアクセス可能：
- Frontend: http://localhost:3000
- Backend API: http://localhost:8080
- Database: localhost:3306

#### 停止・削除
```bash
# コンテナ停止
docker-compose down

# イメージ・ボリューム削除（初期化）
docker-compose down -v
```

### Docker未使用時（ローカル開発）

#### 前提条件
- PHP 8.5.2 以上
- Node.js 18以上
- MySQL/MariaDB 10.5 以上
- npm

#### セットアップ

**1. バックエンド（PHP）**
```bash
# Apache または built-in サーバーで api/ をドキュメントルートに設定
# または Apacheの設定例：
# DocumentRoot: /path/to/STAFT/api
# AllowOverride: All
# <Directory /path/to/STAFT/api>
#   Require all granted
#   RewriteEngine On
#   RewriteCond %{REQUEST_FILENAME} !-f
#   RewriteCond %{REQUEST_FILENAME} !-d
#   RewriteRule ^(.*)$ index.php [QSA,L]
# </Directory>

# PHP built-in サーバーでの起動（開発用）
cd api
php -S localhost:8080
```

**2. フロントエンド（React）**
```bash
# 依存インストール
cd frontend
npm install

# 開発サーバー起動
npm start
# http://localhost:3000 で自動ブラウザ起動
```

**3. データベース**
```bash
# MySQL/MariaDB起動確認
mysql -u root -p

# STAFT用DB・ユーザー作成
CREATE DATABASE staft_db CHARACTER SET utf8mb4;
CREATE USER 'staft_user'@'localhost' IDENTIFIED BY 'staft_password';
GRANT ALL PRIVILEGES ON staft_db.* TO 'staft_user'@'localhost';
FLUSH PRIVILEGES;

# init.sql実行
mysql -u staft_user -p staft_db < docker/db/init.sql
```

#### 設定ファイル修正
`api/common/db.php` の接続情報をローカル環境に合わせる：
```php
$host = 'localhost';
$dbname = 'staft_db';
$username = 'staft_user';
$password = 'staft_password';
```

---

## トラブルシューティング

### ❌ Docker再起動時『Port 3306 is already allocated』

複数のMySQL/MariaDBプロセスが起動している可能性があります。

**解決方法：**
```bash
# 既存コンテナ削除
docker-compose down -v

# ポート使用状況確認
docker ps -a
# または
lsof -i :3306  # macOS/Linux
netstat -ano | findstr :3306  # Windows

# 問題のあるコンテナ停止
docker stop <container-id>
docker rm <container-id>

# 再起動
docker-compose up --build -d
```

### ❌ API接続エラー『localhost:8080 に接続できない』

**確認項目：**
1. PHPコンテナが起動しているか
   ```bash
   docker-compose ps
   ```
2. ホストOSのファイアウォール設定を確認

**解決方法：**
```bash
# PHPコンテナのログ確認
docker-compose logs php

# コンテナ再起動
docker-compose restart php
```

### ❌ テストスクリプト『ECONNREFUSED』エラー

APIサーバーが起動していない可能性があります。

**解決方法：**
```bash
# Docker起動確認
docker-compose ps

# APIエンドポイント手動確認
curl -X GET http://localhost:8080/api/getInfo/auth/me.php -v

# APIサーバー起動確認
docker-compose logs -f php
```

### ❌ データベース『コネクション失敗』

**確認項目：**
1. DBコンテナが起動しているか
   ```bash
   docker-compose ps db
   ```
2. SQLファイルが正しく初期化されたか
   ```bash
   docker-compose logs db
   ```

**解決方法：**
```bash
# DBコンテナ再起動
docker-compose restart db

# 初期化SQLを手動実行
docker exec staft_db mysql -u root -prootpassword staft_db < docker/db/init.sql
```

### ❌ ログイン『認証に失敗しました』

パスワードが異なる可能性があります。デフォルトパスワードを確認してください：

| ユーザー名 | メール | パスワード |
|-----------|--------|-----------|
| sampleuser | sample@example.com | Password123! |
| testuser | test@example.com | Test123! |
| devuser | dev@example.com | Dev123! |
| admin | admin@example.com | Admin123! |

### ❌ CORS エラー『Access-Control-Allow-Origin』

ローカル + Docker通信時に発生する可能性があります。

**解決方法：** `api/common/resp.php` にヘッダー追加
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

### ❌ セッション『ログイン情報が保持されない』

HttpOnly Cookieが正しく設定されていない可能性があります。

**確認：**
1. ブラウザ開発者ツール → Application → Cookies
2. `STAFTSESSID` が存在し `HttpOnly` が有効か確認

**解決方法：**
```bash
# ブラウザキャッシュ・Cookie削除
# 再度 http://localhost:3000 にアクセス
```

### ❌ ポート3000でReactが起動しない『Port already in use』

別のプロセスがポート3000を使用しています。

**解決方法：**
```bash
# ポート使用プロセス確認
lsof -i :3000  # macOS/Linux
netstat -ano | findstr :3000  # Windows

# プロセス終了か別ポートで起動
npm start -- --port 3001
```

### ❌ npm依存パッケージエラー『Cannot find module』

キャッシュが破損している可能性があります。

**解決方法：**
```bash
cd frontend

# キャッシュ削除・再インストール
rm -rf node_modules package-lock.json
npm install

# または
npm ci --force
```

---

## その他のコマンド

### バックエンド（PHP）
```bash
# 構文チェック
php -l api/common/db.php

# 単一ファイルテスト
php api/postInfo/auth/register.php
```

### フロントエンド（React）
```bash
cd frontend

# テスト実行
npm test -- --watchAll=false

# ビルド（本番）
npm run build

# ビルドサイズ確認
npm run build -- --profile
```

### データベース
```bash
# DB接続（Docker経由）
docker exec -it staft_db mysql -u root -prootpassword

# SQL実行
docker exec staft_db mysql -u root -prootpassword staft_db -e "SELECT * FROM users;"
```

---

## 本番環境への推奨設定

1. **test用アカウント削除**
   - init.sqlから testuser / devuser を削除
   
2. **パスワード強化**
   - `.env` に本番用パスワードを設定
   
3. **セッション設定修正**
   - `api/common/auth.php` の `secure` を `true` に
   - `samesite` を `Strict` に
   
4. **エラーログ設定**
   - PHPの error_reporting を本番環境で適切に設定
   
5. **HTTPS強制**
   - Apache/Nginx で .htaccess または Rewrite設定でHTTPS前置
