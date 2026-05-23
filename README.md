# 勤怠管理アプリ

## 概要
出勤・退勤・休憩・勤怠修正申請ができるアプリです。

---

## 機能
- 出勤 / 退勤 / 休憩
- 勤怠一覧表示（月別）
- 勤怠詳細表示
- 修正申請機能
- 管理者による承認機能

## 環境構築

### ① リポジトリクローン
```bash
git clone git clone git@github.com:chiisan-2025/attendance-app.git
cd attendance-app
```
### ② Docker起動
```bash
docker compose up -d
```
### ③ Laravel設定
```bash
docker compose exec php bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## アクセス
http://localhost

## 使用技術
-	Laravel
-	PHP
-	MySQL
-	Docker

## メール認証について

本アプリでは会員登録後にメール認証を実施しています。

開発環境では Mailhog を使用して認証メールを確認してください。

Mailhog:
http://localhost:8025

確認手順：

1. 会員登録を行う
2. `/email/verify` に遷移
3. Mailhogで認証メール確認
4. `Verify Email Address` をクリック
5. `/attendance` に遷移

未認証ユーザーは `/attendance` にアクセスすると
`/email/verify` にリダイレクトされます。

---

## 工夫した点

- 修正申請は承認待ち・承認済みで状態管理し、重複申請を防止しました
- 複数回の休憩取得に対応し、合計休憩時間を自動計算する処理を実装しました
- 未認証ユーザーは `/email/verify` に遷移するよう制御しました
- 時刻の前後関係も考慮したバリデーションを実装しました
---

## ER図
![ER図](https://raw.githubusercontent.com/chiisan-2025/attendance-app/main/attendance-app-docs/er.png)