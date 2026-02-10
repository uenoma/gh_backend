# GH Database API 仕様書

## 概要

このAPIは、GH Databaseのデータを管理するためのRESTful APIです。CRUD操作（作成、読み取り、更新、削除）を提供します。

ベースURL: `https://dndhideout.com/gh/gh_backend/public`

## データモデル

### MobileSuit

| フィールド | タイプ | 必須 | 説明 |
|------------|--------|------|------|
| `data_id` | string | ✅ | データID |
| `ms_number` | string | ✅ | MS番号 |
| `ms_name` | string | ✅ | MS名称 |
| `ms_name_optional` | string | ❌ | オプション名称 |
| `ms_icon` | string | ❌ | アイコン |
| `ms_data` | object | ✅ | MS詳細データ（JSON） |

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

## エンドポイント

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
  }
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

### 4. 更新

**PUT** `/api/mobile-suits/{id}`

指定したIDの機体データを更新します。

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

#### レスポンス

**ステータスコード:** 204 No Content

## エラーハンドリング

### バリデーションエラー

**ステータスコード:** 422 Unprocessable Entity

**ボディ:**
```json
{
  "message": "The data_id field is required.",
  "errors": {
    "data_id": ["The data_id field is required."]
  }
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
    "ms_data": { ... }
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
    "ms_name": "更新された名称",
    ...
  }'
```

#### 削除
```bash
curl -X DELETE http://dndhideout.com/gh/gh_backend/public/api/mobile-suits/1
```

## テストデータ

Seederにより以下のテストデータが登録されています：

- MS-06 (ザクⅡ)
- MSZ-006 (Zガンダム、メガランチャー装備)
- MSZ-010 (ガンダムZZ)

## 注意事項

- `ms_data` フィールドは複雑なJSON構造を持ち、変更時は全体を送信する必要があります
- バリデーションにより必須フィールドのチェックが行われます
- エラー時は適切なHTTPステータスコードとエラーメッセージが返されます