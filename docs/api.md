# GH Database API 仕様書

## 概要

このAPIは、GH Databaseのデータを管理するためのRESTful APIです。CRUD操作（作成、読み取り、更新、削除）を提供します。

ベースURL: `https://dndhideout.com/gh/gh_backend/public`

---

## 認証

このAPIはLaravel Sanctumによるトークン認証を使用します。

認証が必要なエンドポイントには、リクエストヘッダーに以下を付与してください：

```
Authorization: Bearer {token}
```

トークンはユーザー登録またはログイン時にレスポンスとして返されます。

---

## ユーザー管理エンドポイント

### 1. ユーザー登録

**POST** `/api/register`

新しいユーザーアカウントを作成します。成功するとAPIトークンを返します。

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `name` | string | ✅ | アカウント名（英数字・`_` `-` `.` のみ、ユニーク） |
| `email` | string | ✅ | メールアドレス（ユニーク） |
| `password` | string | ✅ | パスワード（8文字以上） |
| `password_confirmation` | string | ✅ | パスワード確認 |

```json
{
  "name": "john_doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### レスポンス

**ステータスコード:** 201 Created

```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "john_doe",
    "email": "john@example.com",
    "created_at": "2026-03-11T00:00:00.000000Z",
    "updated_at": "2026-03-11T00:00:00.000000Z"
  }
}
```

---

### 2. ログイン

**POST** `/api/login`

認証情報を検証し、APIトークンを返します。

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `email` | string | ✅ | メールアドレス |
| `password` | string | ✅ | パスワード |

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "john_doe",
    "email": "john@example.com",
    "created_at": "2026-03-11T00:00:00.000000Z",
    "updated_at": "2026-03-11T00:00:00.000000Z"
  }
}
```

**認証失敗時（422）:**
```json
{
  "message": "メールアドレスまたはパスワードが正しくありません。",
  "errors": {
    "email": ["メールアドレスまたはパスワードが正しくありません。"]
  }
}
```

---

### 3. ログアウト

**POST** `/api/logout` 🔒 *認証必要*

現在のアクセストークンを無効化します。

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "ログアウトしました。"
}
```

---

### 4. 退会

**DELETE** `/api/user` 🔒 *認証必要*

アカウントと全てのトークンを削除します。

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "退会が完了しました。"
}
```

---

## データモデル

### MobileSuit

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `data_id` | string | ✅ | データID |
| `ms_number` | string | ❌ | MS番号（任意） |
| `ms_name` | string | ✅ | MS名称 |
| `ms_name_optional` | string | ❌ | オプション名称 |
| `ms_icon` | string | ❌ | アイコン |
| `ms_data` | object | ✅ | MS詳細データ（JSON） |

#### リクエスト時の追加フィールド

作成・更新リクエスト時に以下のフィールドを追加で送信します：

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `creator_name` | string | ✅ | 作成者名 |
| `edit_password` | string | ✅ | 編集パスワード |

### ms_data 構造

`ms_data` は複雑なネスト構造を持つオブジェクトです。主なプロパティ：

- `spec`: スペック情報
- `receive_types`: 受信タイプ
- `thrusters`: スラスター
- `grapple_types`: 格闘タイプ
- `shooting_types`: 射撃タイプ
- `weapon_specs`: 武器スペック
- `avoidance`: 回避値
- `defence`: 防御値
- `body_part`: ボディパーツ
- `body_specs`: ボディスペック

---

## 機体データエンドポイント

### 1. 一覧取得

**GET** `/api/mobile-suits`

機体データの一覧を取得します。

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:**
```json
[
  {
    "id": 1,
    "data_id": "MS-06",
    "ms_number": "MS-06",
    "ms_name": "ザクⅡ",
    "ms_name_optional": null,
    "ms_icon": "",
    "ms_data": { ... },
    "creator": {
      "creator_name": "作成者名"
    },
    "created_at": "2026-02-10T00:00:00.000000Z",
    "updated_at": "2026-02-10T00:00:00.000000Z"
  }
]
```

### 2. 作成

**POST** `/api/mobile-suits`

新しい機体データを作成します。

#### リクエストボディ

```json
{
  "data_id": "MS-06",
  "ms_number": "MS-06",
  "ms_name": "ザクⅡ",
  "ms_name_optional": "",
  "ms_icon": "",
  "ms_data": {
    "spec": { ... },
    "receive_types": [ ... ],
    ...
  },
  "creator_name": "作成者名",
  "edit_password": "編集パスワード"
}
```

#### レスポンス

**ステータスコード:** 201 Created

**ボディ:** 作成された機体データオブジェクト

### 3. 詳細取得

**GET** `/api/mobile-suits/{id}`

指定したIDの機体データを取得します。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | 機体データID |

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:** 機体データオブジェクト

**例:**
```json
{
  "id": 1,
  "data_id": "MS-06",
  "ms_number": "MS-06",
  "ms_name": "ザクⅡ",
  "ms_name_optional": null,
  "ms_icon": "",
  "ms_data": { ... },
  "creator": {
    "creator_name": "作成者名"
  },
  "created_at": "2026-02-10T00:00:00.000000Z",
  "updated_at": "2026-02-10T00:00:00.000000Z"
}
```

### 4. 更新

**PUT** `/api/mobile-suits/{id}`

指定したIDの機体データを更新します。MobileSuitCreatorが存在しない場合は作成し、存在する場合は作成者名とパスワードの認証を行います。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | 機体データID |

#### リクエストボディ

作成時と同じ構造（全フィールド必須）

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:** 更新された機体データオブジェクト

### 5. 削除

**DELETE** `/api/mobile-suits/{id}`

指定したIDの機体データを削除します。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | 機体データID |

#### リクエストボディ

```json
{
  "creator_name": "作成者名",
  "edit_password": "編集パスワード"
}
```

#### レスポンス

**ステータスコード:** 204 No Content

## エラーハンドリング

### バリデーションエラー

**ステータスコード:** 422 Unprocessable Entity

**ボディ:**
```json
{
  "message": "データIDは必須です (and 1 more error)",
  "errors": {
    "data_id": ["データIDは必須です"],
    "ms_name": ["MS名称は必須です"]
  }
}
```

### 権限エラー

**ステータスコード:** 403 Forbidden

**ボディ:**
```json
{
  "message": "作成者名またはパスワードが正しくありません"
}
```

### リソース未発見

**ステータスコード:** 404 Not Found

**ボディ:**
```json
{
  "message": "No query results for model [App\\Models\\MobileSuit] 999"
}
```

## 使用例

### cURLでの使用例

#### ユーザー登録
```bash
curl -X POST http://dndhideout.com/gh/gh_backend/public/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "john_doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### ログイン
```bash
curl -X POST http://dndhideout.com/gh/gh_backend/public/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### ログアウト
```bash
curl -X POST http://dndhideout.com/gh/gh_backend/public/api/logout \
  -H "Authorization: Bearer {token}"
```

#### 退会
```bash
curl -X DELETE http://dndhideout.com/gh/gh_backend/public/api/user \
  -H "Authorization: Bearer {token}"
```

#### 一覧取得
```bash
curl -X GET http://dndhideout.com/gh/gh_backend/public/api/mobile-suits
```

#### 作成
```bash
curl -X POST http://dndhideout.com/gh/gh_backend/public/api/mobile-suits \
  -H "Content-Type: application/json" \
  -d '{
    "data_id": "MS-06",
    "ms_number": "MS-06",
    "ms_name": "ザクⅡ",
    "ms_data": { ... },
    "creator_name": "作成者名",
    "edit_password": "編集パスワード"
  }'
```

#### 詳細取得
```bash
curl -X GET http://dndhideout.com/gh/gh_backend/public/api/mobile-suits/1
```

#### 更新
```bash
curl -X PUT http://dndhideout.com/gh/gh_backend/public/api/mobile-suits/1 \
  -H "Content-Type: application/json" \
  -d '{
    "data_id": "MS-06",
    "ms_number": "MS-06",
    "ms_name": "更新された名称",
    "ms_data": { ... },
    "creator_name": "作成者名",
    "edit_password": "編集パスワード"
  }'
```

#### 削除
```bash
curl -X DELETE http://dndhideout.com/gh/gh_backend/public/api/mobile-suits/1 \
  -H "Content-Type: application/json" \
  -d '{
    "creator_name": "作成者名",
    "edit_password": "編集パスワード"
  }'
```

## テストデータ

Seederにより以下のテストデータが登録されています：

- MS-06 (ザクⅡ)
- MSZ-006 (Zガンダム、メガランチャー装備)
- MSZ-010 (ガンダムZZ)

## 注意事項

- `ms_data` フィールドは複雑なJSON構造を持ち、変更時は全体を送信する必要があります
- `ms_number` は任意フィールドです
- `creator_name` と `edit_password` は作成・更新・削除時に必須です（レスポンスには含まれません）
- `edit_password` はハッシュ化されて保存されます
- 更新・削除時は作成者名とパスワードが一致しない場合、403エラーが返されます
- 更新時はMobileSuitCreatorが存在しない場合は作成し、存在する場合は認証を行います
- 一覧取得と詳細取得のレスポンスには`creator`オブジェクト（`creator_name`のみ）が含まれます
- バリデーションにより必須フィールドのチェックが行われます
- エラー時は適切なHTTPステータスコードとエラーメッセージが返されます