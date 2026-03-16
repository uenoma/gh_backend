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

### 5. パスワードリセットメール送信

**POST** `/api/forgot-password`

パスワードリセット用のメールを送信します。登録済みのメールアドレスにリセットリンクが送られます。

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `email` | string | ✅ | 登録済みのメールアドレス |

```json
{
  "email": "john@example.com"
}
```

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "パスワードリセットメールを送信しました。"
}
```

**バリデーションエラー時（422）:**
```json
{
  "message": "...",
  "errors": {
    "email": ["..."]
  }
}
```

---

### 6. パスワードリセット実行

**POST** `/api/reset-password`

メールで受け取ったトークンを使用してパスワードをリセットします。

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `token` | string | ✅ | メールで受け取ったリセットトークン |
| `email` | string | ✅ | 登録済みのメールアドレス |
| `password` | string | ✅ | 新しいパスワード（8文字以上） |
| `password_confirmation` | string | ✅ | 新しいパスワード確認 |

```json
{
  "token": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "パスワードをリセットしました。"
}
```

**トークン無効時（422）:**
```json
{
  "message": "...",
  "errors": {
    "email": ["..."]
  }
}
```

---

### 7. パスワード変更

**PUT** `/api/user/password` 🔒 *認証必要*

現在のパスワードを確認した上で新しいパスワードに変更します。

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `current_password` | string | ✅ | 現在のパスワード |
| `password` | string | ✅ | 新しいパスワード（8文字以上） |
| `password_confirmation` | string | ✅ | 新しいパスワード確認 |

```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "パスワードを変更しました。"
}
```

**現在のパスワード誤り時（422）:**
```json
{
  "message": "現在のパスワードが正しくありません。",
  "errors": {
    "current_password": ["現在のパスワードが正しくありません。"]
  }
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

## ゲームセッションエンドポイント

### 1. ゲームセッション一覧取得

**GET** `/api/game-sessions`

ゲームセッションの一覧を新しい順に取得します。

#### レスポンス

**ステータスコード:** 200 OK

```json
[
  {
    "id": 1,
    "user_id": 1,
    "name": "初心者歓迎セッション",
    "description": "初心者の方も安心してご参加ください。",
    "capacity": 4,
    "created_at": "2026-03-11T00:00:00.000000Z",
    "updated_at": "2026-03-11T00:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "john_doe"
    },
    "members": [
      {
        "id": 1,
        "name": "john_doe",
        "pivot": {
          "game_session_id": 1,
          "user_id": 1,
          "joined_at": "2026-03-11T00:00:00.000000Z",
          "mobile_suit_id": null
        }
      }
    ]
  }
]
```

---

### 2. ゲームセッション詳細取得

**GET** `/api/game-sessions/{id}`

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:** ゲームセッションオブジェクト（一覧取得と同じ構造、`members` 配列を含む）

---

### 3. ゲームセッション作成

**POST** `/api/game-sessions` 🔒 *認証必要*

作成者はトークンから自動的に紐付けられます。

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `name` | string | ✅ | セッション名（255文字以内） |
| `description` | string | ❌ | 説明文 |
| `capacity` | integer | ✅ | 定員数（1以上） |

```json
{
  "name": "初心者歓迎セッション",
  "description": "初心者の方も安心してご参加ください。",
  "capacity": 4
}
```

#### レスポンス

**ステータスコード:** 201 Created

**ボディ:** 作成されたゲームセッションオブジェクト

---

### 4. ゲームセッション編集

**PATCH** `/api/game-sessions/{id}` 🔒 *認証必要*

自分が作成したセッションのみ編集可能です。送信したフィールドのみ更新されます。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `name` | string | ❌ | セッション名（255文字以内） |
| `description` | string | ❌ | 説明文 |
| `capacity` | integer | ❌ | 定員数（1以上） |

```json
{
  "name": "更新後のセッション名",
  "capacity": 6
}
```

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:** 更新されたゲームセッションオブジェクト

**権限エラー時（403）:**
```json
{
  "message": "編集する権限がありません。"
}
```

---

### 5. ゲームセッション削除

**DELETE** `/api/game-sessions/{id}` 🔒 *認証必要*

自分が作成したセッションのみ削除可能です。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "ゲームセッションを削除しました。"
}
```

**権限エラー時（403）:**
```json
{
  "message": "削除する権限がありません。"
}
```

---

### 6. ゲームセッションへ参加

**POST** `/api/game-sessions/{id}/join` 🔒 *認証必要*

指定したゲームセッションに参加します。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:** 更新されたゲームセッションオブジェクト（`members` 根拠み）

**エラー時（409）:**
```json
{ "message": "既に参加しています。" }
```
```json
{ "message": "定員に達しています。" }
```

---

### 7. ゲームセッションから離脱

**DELETE** `/api/game-sessions/{id}/leave` 🔒 *認証必要*

参加中のゲームセッションから離脱します。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "ゲームセッションから離脱しました。"
}
```

**エラー時（409）:**
```json
{ "message": "参加していません。" }
```

---

### 8. 使用機体の選択

**PUT** `/api/game-sessions/{id}/mobile-suit` 🔒 *認証必要*

参加中のゲームセッションで使用する機体を選択または解除します。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `mobile_suit_id` | integer\|null | ❌ | 機体ID（`null` で選択解除） |

```json
{
  "mobile_suit_id": 1
}
```

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:** 更新されたゲームセッションオブジェクト（`members` 内の `pivot.mobile_suit_id` に反映）

**権限エラー時（403）:**
```json
{ "message": "このセッションに参加していません。" }
```

---

### 9. パイロットポイントの更新

**PUT** `/api/game-sessions/{id}/pilot-point` 🔒 *認証必要*

参加中のゲームセッションでの自分のパイロットポイントを更新します。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `pilot_point` | integer | ✅ | パイロットポイント（0以上） |

```json
{
  "pilot_point": 5
}
```

#### レスポンス

**ステータスコード:** 200 OK

**ボディ:** 更新されたゲームセッションオブジェクト

**権限エラー時（403）:**
```json
{ "message": "このセッションに参加していません。" }
```

---

### 10. セッションレポート取得

**GET** `/api/game-sessions/{id}/report`

指定したゲームセッションの参加者・使用機体・パイロットポイントを含むレポートを取得します。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "id": 1,
  "name": "初心者歓迎セッション",
  "description": "初心者の方も安心してご参加ください。",
  "capacity": 4,
  "user": {
    "id": 1,
    "name": "john_doe"
  },
  "members": [
    {
      "id": 1,
      "name": "john_doe",
      "pilot_point": 7,
      "joined_at": "2026-03-16T00:00:00.000000Z",
      "mobile_suit": {
        "id": 1,
        "ms_number": "MS-06",
        "ms_name": "ザクⅡ",
        "ms_name_optional": null,
        "ms_icon": ""
      },
      "plots": {
        "0": {
          "plot": { "hex": "0312", "direction": 3, "altitude": 0, "inertia": ["1-2"] },
          "damage": null
        },
        "1": {
          "plot": { "hex": "0211", "direction": 2, "altitude": -1, "inertia": ["3-5", "u-2"] },
          "damage": [
            { "description": "右腕部に2ダメージ" },
            { "description": "脚部に1ダメージ" }
          ]
        }
      }
    },
    {
      "id": 2,
      "name": "jane_doe",
      "pilot_point": 7,
      "joined_at": "2026-03-16T00:01:00.000000Z",
      "mobile_suit": null,
      "plots": {}
    }
  ]
}
```

---

### 11. イニングのPlot登録・更新

**PUT** `/api/game-sessions/{id}/plots/{inning}` 🔒 *認証必要*

指定したイニングの行動計画（plot）および損害状況（damage）を登録または更新します。既にレコードが存在する場合は上書きされます。

#### パラメータ

| パラメータ | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `id` | integer | ✅ | ゲームセッションID |
| `inning` | integer | ✅ | イニング番号（0〜99） |

#### リクエストボディ

`plot` と `damage` はそれぞれ独立して送信可能です（片方だけの更新も可）。

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `plot` | object\|null | ❌ | 行動計画JSONオブジェクト |
| `plot.hex` | string | ❌ | 現在位置・ヘックス番号（4桁数字、例: `"0312"`） |
| `plot.direction` | integer | ❌ | 方向（1〜6） |
| `plot.altitude` | integer | ❌ | 高度（マイナスあり） |
| `plot.inertia` | array\|null | ❌ | 慣性（最大3つ、例: `["1-2", "3-5"]`） |
| `plot.inertia[]` | string | ❌ | 慣性1件（`数字またはu` + `-` + `数字`、例: `"1-2"`, `"u-2"`） |
| `damage` | array\|null | ❌ | 損害状況の配列（複数の損害を記録可） |
| `damage[].` | object | ❌ | 損害1件分のJSONオブジェクト |

```json
{
  "plot": {
    "hex": "0312",
    "direction": 3,
    "altitude": -1,
    "inertia": ["1-2", "3-5", "u-2"]
  },
  "damage": [
    { "description": "右腕部に2ダメージ" },
    { "description": "脚部に1ダメージ" }
  ]
}
```

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "id": 1,
  "game_session_id": 1,
  "user_id": 1,
  "inning": 0,
  "plot": {
    "hex": "0312",
    "direction": 3,
    "altitude": -1,
    "inertia": ["1-2", "3-5", "u-2"]
  },
  "damage": [
    { "description": "右腕部に2ダメージ" },
    { "description": "脚部に1ダメージ" }
  ],
  "created_at": "2026-03-16T00:00:00.000000Z",
  "updated_at": "2026-03-16T00:00:00.000000Z"
}
```

**権限エラー時（403）:**
```json
{ "message": "このセッションに参加していません。" }
```

**イニング範囲外時（422）:**
```json
{ "message": "inningは0〜99の範囲で指定してください。" }
```

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

---

## チャットチャンネルエンドポイント

全エンドポイントで認証（`Authorization: Bearer {token}`）が必要です。

### 1. チャンネル一覧取得

**GET** `/api/chat-channels`

全チャンネルの一覧を返します。参加しているチャンネルには `unread_count`（未読メッセージ件数）が付与されます。未参加チャンネルの `unread_count` は `0` です。

#### レスポンス

**ステータスコード:** 200 OK

```json
[
  {
    "id": 1,
    "name": "general",
    "description": "一般チャンネル",
    "user_id": null,
    "is_system": true,
    "created_at": "2026-03-13T00:00:00.000000Z",
    "updated_at": "2026-03-13T00:00:00.000000Z",
    "creator": null
  }
]
```

---

### 2. チャンネル詳細取得

**GET** `/api/chat-channels/{id}`

指定したチャンネルの詳細を返します。参加している場合は `unread_count`（未読メッセージ件数）が付与されます。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `id` | integer | チャンネルID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "id": 2,
  "name": "game-talk",
  "description": "ゲームについて話しましょう",
  "user_id": 1,
  "is_system": false,
  "created_at": "2026-03-13T00:00:00.000000Z",
  "updated_at": "2026-03-13T00:00:00.000000Z",
  "creator": {
    "id": 1,
    "name": "john_doe"
  },
  "members": [
    {
      "id": 1,
      "name": "john_doe",
      "pivot": {
        "chat_channel_id": 2,
        "user_id": 1,
        "joined_at": "2026-03-13T00:00:00.000000Z"
      }
    }
  ]
}
```

---

### 3. チャンネル作成

**POST** `/api/chat-channels`

新しいチャンネルを作成します。

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `name` | string | ✅ | チャンネル名（ユニーク、最大100文字） |
| `description` | string | - | チャンネルの説明（最大500文字） |

```json
{
  "name": "game-talk",
  "description": "ゲームについて話しましょう"
}
```

#### レスポンス

**ステータスコード:** 201 Created

```json
{
  "id": 2,
  "name": "game-talk",
  "description": "ゲームについて話しましょう",
  "user_id": 1,
  "is_system": false,
  "created_at": "2026-03-13T00:00:00.000000Z",
  "updated_at": "2026-03-13T00:00:00.000000Z",
  "creator": {
    "id": 1,
    "name": "john_doe"
  }
}
```

---

### 4. チャンネル更新

**PATCH** `/api/chat-channels/{id}`

チャンネルの情報を更新します。作成者本人のみ操作可能です。システムチャンネル（`is_system: true`）は更新不可です。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `id` | integer | チャンネルID |

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `name` | string | - | チャンネル名（ユニーク、最大100文字） |
| `description` | string | - | チャンネルの説明（最大500文字） |

```json
{
  "description": "更新された説明文"
}
```

#### レスポンス

**ステータスコード:** 200 OK

更新後のチャンネルオブジェクトを返します。

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 403 Forbidden | システムチャンネルへの操作、または作成者以外による操作 |
| 404 Not Found | チャンネルが存在しない |

---

### 5. チャンネル削除

**DELETE** `/api/chat-channels/{id}`

チャンネルを削除します。作成者本人のみ操作可能です。システムチャンネル（`is_system: true`）は削除不可です。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `id` | integer | チャンネルID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "チャンネルを削除しました。"
}
```

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 403 Forbidden | システムチャンネルへの操作、または作成者以外による操作 |
| 404 Not Found | チャンネルが存在しない |

---

### 6. チャンネル参加

**POST** `/api/chat-channels/{id}/join`

指定したチャンネルに参加します。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `id` | integer | チャンネルID |

#### レスポンス

**ステータスコード:** 200 OK

参加後のチャンネルオブジェクト（`members` を含む）を返します。

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 404 Not Found | チャンネルが存在しない |
| 409 Conflict | 既に参加している |

---

### 7. チャンネル退出

**DELETE** `/api/chat-channels/{id}/leave`

参加中のチャンネルから退出します。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `id` | integer | チャンネルID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "チャンネルから退出しました。"
}
```

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 404 Not Found | チャンネルが存在しない |
| 409 Conflict | 参加していない |

---

### 8. 既読マーク

**POST** `/api/chat-channels/{id}/read`

指定チャンネルの全メッセージを既読にします（`last_read_at` を現在時刻に更新）。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `id` | integer | チャンネルID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "既読にしました。"
}
```

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 403 Forbidden | チャンネルに参加していない |
| 404 Not Found | チャンネルが存在しない |

---

## チャットメッセージエンドポイント

全エンドポイントで認証（`Authorization: Bearer {token}`）が必要です。
メッセージの取得・投稿はチャンネルメンバーのみ操作可能です。

### 1. メッセージ一覧取得

**GET** `/api/chat-channels/{channelId}/messages`

指定チャンネルのメッセージを最新順で返します。カーソルページネーション（1ページ50件）。**メッセージ取得時に `last_read_at` が自動更新され、チャンネルが既読状態になります。**

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `channelId` | integer | チャンネルID |

#### クエリパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `cursor` | string | 次ページ取得用カーソル（レスポンスの `next_cursor` を使用） |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "data": [
    {
      "id": 3,
      "chat_channel_id": 1,
      "user_id": 1,
      "body": "こんにちは！",
      "created_at": "2026-03-13T00:00:01.000000Z",
      "updated_at": "2026-03-13T00:00:01.000000Z",
      "user": {
        "id": 1,
        "name": "john_doe"
      }
    }
  ],
  "path": "https://example.com/api/chat-channels/1/messages",
  "per_page": 50,
  "next_cursor": "eyJpZCI6MX0",
  "next_page_url": "https://example.com/api/chat-channels/1/messages?cursor=eyJpZCI6MX0",
  "prev_cursor": null,
  "prev_page_url": null
}
```

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 403 Forbidden | チャンネルに参加していない |
| 404 Not Found | チャンネルが存在しない |

---

### 2. メッセージ投稿

**POST** `/api/chat-channels/{channelId}/messages`

指定チャンネルにメッセージを投稿します。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `channelId` | integer | チャンネルID |

#### リクエストボディ

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `body` | string | ✅ | メッセージ本文（最大2000文字） |

```json
{
  "body": "こんにちは！"
}
```

#### レスポンス

**ステータスコード:** 201 Created

```json
{
  "id": 1,
  "chat_channel_id": 1,
  "user_id": 1,
  "body": "こんにちは！",
  "created_at": "2026-03-13T00:00:00.000000Z",
  "updated_at": "2026-03-13T00:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "john_doe"
  }
}
```

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 403 Forbidden | チャンネルに参加していない |
| 404 Not Found | チャンネルが存在しない |

---

### 3. メッセージ削除

**DELETE** `/api/chat-channels/{channelId}/messages/{messageId}`

自分が投稿したメッセージを削除します。

#### パスパラメータ

| パラメータ | タイプ | 説明 |
|------------|--------|------|
| `channelId` | integer | チャンネルID |
| `messageId` | integer | メッセージID |

#### レスポンス

**ステータスコード:** 200 OK

```json
{
  "message": "メッセージを削除しました。"
}
```

#### エラー

| ステータスコード | 説明 |
|-----------------|------|
| 403 Forbidden | 投稿者以外による操作 |
| 404 Not Found | チャンネルまたはメッセージが存在しない |

---

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