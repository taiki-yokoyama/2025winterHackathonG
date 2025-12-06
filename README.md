# TeamSync

## 必要な環境

- PHP 8.2以上
- Composer
- Node.js & npm
- MySQL 8.4

## セットアップ手順

### 1. 依存関係のインストール

```bash
composer install
npm install
```

### 2. 環境設定

```bash
# .envファイルを作成
copy .env.example .env

# アプリケーションキーを生成
php artisan key:generate
```

### 3. データベース設定

`.env`ファイルを開いて、データベース接続情報を設定してください。

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teamsync
DB_USERNAME=root
DB_PASSWORD=
```

### 4. データベースのマイグレーション

```bash
php artisan migrate
```

### 5. フロントエンドのビルド

```bash
npm run build
```

## 起動方法

### 開発サーバーの起動

```bash
php artisan serve
```

ブラウザで `http://localhost:8000` にアクセスしてください。

### フロントエンド開発モード

別のターミナルで以下を実行すると、ファイル変更時に自動でリビルドされます。

```bash
npm run dev
```

### まとめて起動（推奨）

サーバー、キュー、ログ、Viteを一度に起動できます。

```bash
composer dev
```

## Dockerを使う場合

Laravel Sailを使ってDockerで起動することもできます。

```bash
# 初回のみ
composer require laravel/sail --dev

# Sailのインストール
php artisan sail:install

# 起動
./vendor/bin/sail up
```

起動後は `http://localhost` でアクセスできます。

## テストの実行

```bash
composer test
```

または

```bash
php artisan test
```
