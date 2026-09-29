# vlog-app

日々の記録を投稿・公開できる、個人ブログ（Vlog）Web アプリケーションです。
Laravel の学習と Web 開発の成果物として、認証・投稿管理・タグ・画像アップロード・
カレンダー表示までを一通り実装しています。

---

## 概要

- ログインしたユーザーが記事を作成・編集・削除できるブログアプリ
- 記事にはタイトル・本文・画像・タグ・公開/非公開を設定できる
- 記事の一覧・詳細・カレンダーは**ログインなし（ゲスト）でも閲覧できる**公開ブログ構成
- 月間カレンダーから、その日に投稿された公開記事へたどれる

「書く人（自分）はログインして投稿し、読む人（ネットの訪問者）はログインなしで閲覧する」
という、公開ブログとして自然な権限設計にしています。

---

## 背景・目的

Web アプリケーション開発を学ぶための成果物として制作しました。単なるチュートリアルの
写経ではなく、以下を意識して段階的に機能を積み上げています。

- **フレームワークの基本を体で覚える** — ルーティング、コントローラ、Eloquent ORM、
  Blade テンプレート、マイグレーション、リレーション（1対多・多対多）
- **実運用で必要になる要素を一通り触る** — 認証、認可（ログイン必須の出し分け）、
  ファイルアップロード、公開/非公開の制御、タイムゾーン対応
- **チーム開発を想定したブランチ運用** — `feature/*` → `develop` → `master` の
  git-flow スタイルで、機能ごとにブランチを切って開発

各機能の実装過程や学びは `docs/learning-log/` に学習ログとして残しています。

---

## 使用技術

### バックエンド
| 技術 | バージョン | 用途 |
|---|---|---|
| PHP | 8.3+ | 言語 |
| Laravel | 13.x | Web アプリケーションフレームワーク |
| Laravel Breeze | 2.x | 認証スキャフォールディング（ログイン/登録/プロフィール） |
| SQLite | - | データベース（デフォルト） |
| Eloquent ORM | - | データベース操作・リレーション |

### フロントエンド
| 技術 | バージョン | 用途 |
|---|---|---|
| Blade | - | サーバーサイドテンプレート |
| Tailwind CSS | 3.x | スタイリング（ユーティリティファースト） |
| Alpine.js | 3.x | 軽量な UI インタラクション（メニュー開閉など） |
| Vite | 8.x | アセットのビルド・開発サーバー |

### 開発・品質
| 技術 | 用途 |
|---|---|
| Pest | テストフレームワーク |
| Laravel Pint | コードフォーマッタ |
| Laravel Pail | ログ閲覧 |
| Tinker / Faker | 対話実行・ダミーデータ生成 |

---

## 主な機能

- **認証**（Laravel Breeze）：ユーザー登録・ログイン・プロフィール編集
- **投稿管理（CRUD）**：記事の作成・一覧・詳細・編集・削除
  - タイトル・本文・画像・公開フラグ（`is_public`）を設定可能
  - 作成・編集・削除はログイン必須、一覧・詳細は誰でも閲覧可能
- **画像アップロード**：記事にサムネイル画像を添付（`image_path`）
- **タグ機能**：記事に複数タグを付与（投稿とタグの多対多）
- **カレンダー**：月間カレンダーに公開記事を表示
  - 前月・次月へ移動
  - 記事がある日に記事タイトルをリンク表示
  - 曜日の色分け（日曜=赤／土曜=青）・今日のハイライト
  - タイムゾーンは `Asia/Tokyo`（日付判定のズレ対策）

---

## データモデル

```
User 1 ──< Post >── Tag （多対多：post_tag 中間テーブル）
         (1対多)
```

- **User** … ユーザー。複数の Post を持つ
- **Post** … 記事。`user_id` / `title` / `body` / `image_path` / `is_public`。
  User に属し（多対一）、複数の Tag を持つ（多対多）
- **Tag** … タグ。複数の Post に紐づく（多対多）

---

## セットアップ

前提：PHP 8.3+ / Composer / Node.js

```bash
# 1. 依存パッケージのインストール
composer install
npm install

# 2. 環境設定
cp .env.example .env
php artisan key:generate

# 3. データベース（SQLite）の初期化
touch database/database.sqlite   # 未作成の場合
php artisan migrate

# 4. 開発サーバーの起動（別ターミナルで）
npm run dev          # Vite（アセットのビルド／HMR）
php artisan serve    # アプリケーションサーバー（http://localhost:8000）
```

> **Note:** Tailwind のクラスを新しく追加したときは `npm run dev`（Vite）を
> 起動しておくこと。使われているクラスだけ CSS に書き出されるため、
> dev 未起動＋古いビルドのままだと新しいクラスが効かない。

---

## 管理者と API

### 管理者
投稿の**作成・編集・削除は管理者（`users.is_admin = true`）のみ**が行えます。
一般ユーザーは閲覧のみで、書き込み系のルートは 403 になります。
管理者は自分以外が書いた投稿も編集・削除できます。

管理者にしたいユーザーは、先に会員登録（`/register`）してから、次のコマンドで昇格させます。

```bash
php artisan user:make-admin you@example.com
```

メールアドレスのユーザーが見つからない場合はエラーになります。
`is_admin` は会員登録やプロフィール編集のフォームからは変更できません（一括代入の対象外）。

### API（Laravel Sanctum）
| メソッド | パス | 認可 |
|---|---|---|
| GET | `/api/posts` | 誰でも（公開投稿のみ） |
| GET | `/api/posts/{id}` | 誰でも（非公開は投稿者本人のみ、それ以外は 404） |
| POST | `/api/posts` | 管理者（トークン必須） |
| PUT | `/api/posts/{id}` | 管理者（トークン必須） |
| DELETE | `/api/posts/{id}` | 管理者（トークン必須） |
| GET | `/api/user` | ログイン済み（トークン必須） |

トークンは Tinker で発行し、`Authorization: Bearer <token>` ヘッダで送ります。

```bash
php artisan tinker
>>> App\Models\User::where('email', 'you@example.com')->first()->createToken('cli')->plainTextToken;
```

```bash
curl -H "Authorization: Bearer <token>" -H "Accept: application/json" http://localhost:8000/api/user
```

---

## 本番デプロイの設定

本番用の `.env` の雛形は `.env.production.example` です。サーバー上でコピーして値を埋めます。

```bash
cp .env.production.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

- `APP_ENV=production` / `APP_DEBUG=false`（エラー画面に内部情報を出さない）
- `APP_URL` は `https://` で始める。本番では生成される URL も自動で https になる
- `SESSION_ENCRYPT=true`（セッションを暗号化）/ `SESSION_SECURE_COOKIE=true`（HTTPS でのみ Cookie を送る）
- メールは `log` ではなく実際の SMTP を設定する（パスワードリセットのメールが送られる）
- HTTP から HTTPS への転送は、Web サーバーまたはホスティング側で設定する

確認には `php artisan about` で `Environment` と `Debug Mode` を見ます。

### 共有レンタルサーバーへのデプロイ

管理画面の名前や細かい設定はサービスごとに違うため、共通する手順だけを書きます。

**契約前の条件**
- PHP 8.3 以上が使える
- SSH で接続できる（`migrate` や `user:make-admin` の実行に必要）
- 拡張機能: `pdo_sqlite` / `mbstring` / `openssl` / `fileinfo` / `tokenizer` / `xml` / `ctype`

**1. 手元でアセットをビルドする**

サーバーに Node が無いことが多いため、手元でビルドします。`public/build` は Git に入らないので、サーバーへ別にアップロードします。

```bash
npm ci && npm run build
```

**2. ファイルを置く**

公開してよいのは `public/` の中身だけです。プロジェクト全体は、公開ディレクトリの**外**に置きます。

```
/home/あなた/vlog-app/        ← プロジェクト全体（外から見えない場所）
/home/あなた/public_html/     ← 公開ディレクトリ（vlog-app/public を指すようにする）
```

公開ディレクトリを `vlog-app/public` に向けられない場合は、`public/` の中身を公開ディレクトリに置き、
`index.php` 内の `vendor` と `bootstrap` へのパスを書き換えます。

**3. サーバー上で設定する（SSH）**

```bash
cd ~/vlog-app
composer install --no-dev --optimize-autoloader
cp .env.production.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
ln -s ../storage/app/public public/storage
chmod -R 775 storage bootstrap/cache database
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

`.env` は次の値を書き換えます。
- `APP_URL=https://あなたのドメイン`
- メールの SMTP（サーバーが案内する値）
- `QUEUE_CONNECTION=sync`（共有サーバーではキューの処理役を常駐させられない。`database` のままだとジョブが実行されない）

**4. HTTPS を有効にする**

管理画面でドメインの無料 SSL 証明書を有効にし、`http://` から `https://` への転送を設定します
（管理画面の設定、または `public/.htaccess` に転送ルールを追加）。

**5. 管理者を作る**

先にサイトから会員登録してから、次を実行します。

```bash
php artisan user:make-admin you@example.com
```

**6. 公開後の確認（セキュリティ）**

外から見えてはいけないファイルが見えていないかを確認します。すべて 404 か 403 になれば合格です。
`200` が返ったものは公開ディレクトリの設定が間違っているので、すぐに直してください。

```bash
curl -I https://あなたのドメイン/.env
curl -I https://あなたのドメイン/database/database.sqlite
curl -I https://あなたのドメイン/storage/logs/laravel.log
curl -I https://あなたのドメイン/vendor/autoload.php
```

そのうえで、`php artisan about` で `Environment: production` / `Debug Mode: OFF` を確認し、
ブラウザの DevTools で Cookie に `Secure` が付いていることを確認します。

**運用**
- バックアップ: `database/database.sqlite` と `storage/app/public/images/` を定期的にダウンロードする
- 更新時:
  ```bash
  composer install --no-dev --optimize-autoloader
  php artisan migrate --force
  php artisan config:cache && php artisan route:cache && php artisan view:cache
  ```

---

## テストと CI

```bash
php artisan test
```

認可（IDOR）のテストは `tests/Feature/PostAuthorizationTest.php` にあります。
`master` / `develop` への push と PR で、GitHub Actions（`.github/workflows/tests.yml`）が
アセットをビルドしてテストを自動実行します。

---

## ブランチ運用

git-flow スタイルで開発しています。

- `master` … リリース用の安定ブランチ
- `develop` … 開発の集約先
- `feature/*` … 機能ごとの開発ブランチ

新しい機能は `develop` から `feature/xxx` を切って開発し、
**PR は `develop` に向けて**作成します（`master` はリリース時にまとめて更新）。

---

## ディレクトリメモ

- `app/Http/Controllers/` … `PostController` / `CalendarController` / `ProfileController` など
- `app/Models/` … `User` / `Post` / `Tag`
- `resources/views/` … Blade テンプレート（`layouts/`・`posts/`・`calendar/` など）
- `database/migrations/` … テーブル定義
- `docs/learning-log/` … 実装の学習ログ（Git 管理外）
