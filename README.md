# coachtech-attendance-app

COACHTECHの模擬案件として制作した
勤怠アプリです。
本アプリケーションは、スタッフの勤怠打刻および勤怠修正申請を行うための勤怠管理システムです。
管理者側の画面と一般ユーザー側の画面に別れています。
※ 本READMEは、環境構築手順・動作確認方法・設計資料の参照を目的として記載しています。

## 環境構築手順

## 1 リポジトリのクローン

```bash
git clone git@github.com:yurinaniko/coachtech-attendance-app.git
cd coachtech-attendance-app
```

## 2 Docker 起動

```bash
docker compose up -d --build
```

## 3 PHP コンテナに入る

```bash
docker compose exec php bash
```

## 4 依存関係のインストール（Composer）

```bash
composer install
```

## 5 .env ファイル作成

```bash
cp .env.example .env
php artisan key:generate
```

## 6 .env 設定

### ① アプリケーション設定

```env
APP_NAME=coachtech勤怠管理アプリ
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### ② データベース設定（Docker）

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

### ③　 メール設定（MailHog）

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@example.com
MAIL_FROM_NAME="coachtech勤怠管理アプリ"
```

## 7 データベース初期化（マイグレーション & シーディング）

```bash
php artisan migrate:fresh --seed
```

## 8 アプリケーション確認

以下のURLにアクセスすると、アプリケーションが表示されます。

```
http://localhost:8000
```

## 備考

※（M1 / M2 Mac）
本プロジェクトでは、Apple Silicon（M1 / M2 Mac）環境でも
問題なく動作するよう、docker-compose.yml にて
ARM64 対応の Docker image を使用しています。

```yaml
mysql:
  image: arm64v8/mysql:8.0
  platform: linux/arm64/v8
```

そのため、M1 / M2 Mac 環境でも
追加設定なしで Docker を起動できます。

## 動作確認用アカウント

Seeder により以下のテストユーザーを用意しています。

### ■ 管理者

メールアドレス: admin@example.com
パスワード: password123

### ■ 一般ユーザー

メールアドレス：user@example.com
パスワード：password456

※ テストを円滑に行うため、Seeder 側で メール認証済み状態 にしています。
ログイン画面からお試しください。

### メール認証（MailHog）

開発環境では MailHog を使用しています。

```
アプリ： http://localhost:8000

MailHog： http://localhost:8025
```

メール認証・通知メールは MailHog 上で確認できます。
メール認証誘導画面の認証はこちらからのボタンを押すとメール認証画面（MailHog画面）に遷移されます。

## ER 図

## テーブル仕様

本アプリのテーブル設計は以下のドキュメントにまとめています。

## ルート・コントローラー・ビュー構成

画面ごとのルーティング、コントローラー、アクション対応、ビューは
以下のドキュメントにまとめています。

## 使用技術

- 種類 バージョン
- PHP 8.x
- Laravel 8.x
- Laravel Fortify（認証機能）
- MySQL 8.0
- Nginx 1.25
- Docker / Docker Compose 最新
- MailHog 開発用
- phpMyAdmin 使用

## 機能一覧
※ 本アプリケーションでは、管理者・一般ユーザー間で
利用可能機能を権限により制御しています。

### 一般ユーザーが使用できる機能
- 会員登録
- ログイン機能
- 勤怠打刻機能
- 休憩打刻機能
- 勤怠一覧表示
- 勤怠詳細表示
- 勤怠修正申請機能

---

### 管理者が使用できる機能
- ログイン機能
- 勤怠一覧表示
- 勤怠詳細表示
- スタッフ一覧表示
- スタッフ別勤怠一覧表示（日別 / 月別）
- 勤怠データCSV出力
- 申請一覧表示
- 修正申請承認機能

## 実装した応用機能

- メール認証機能（mailhog）
- 認証メール再送機能
- CSV出力機能

## バリデーションについて
本アプリケーションでは Laravel FormRequest を使用しています。