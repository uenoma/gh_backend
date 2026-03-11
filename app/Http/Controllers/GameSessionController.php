<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use Illuminate\Http\Request;

class GameSessionController extends Controller
{
    /**
     * ゲームセッション一覧取得
     */
    public function index()
    {
        $sessions = GameSession::with('user:id,name')->latest()->get();
        return response()->json($sessions);
    }

    /**
     * ゲームセッション詳細取得
     */
    public function show(string $id)
    {
        $session = GameSession::with('user:id,name')->findOrFail($id);
        return response()->json($session);
    }

    /**
     * ゲームセッション作成（要認証）
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity'    => ['required', 'integer', 'min:1'],
        ]);

        $session = $request->user()->gameSessions()->create($validated);

        return response()->json($session->load('user:id,name'), 201);
    }

    /**
     * ゲームセッション削除（要認証・自分のセッションのみ）
     */
    public function destroy(Request $request, string $id)
    {
        $session = GameSession::findOrFail($id);

        if ($session->user_id !== $request->user()->id) {
            return response()->json(['message' => '削除する権限がありません。'], 403);
        }

        $session->delete();

        return response()->json(['message' => 'ゲームセッションを削除しました。']);
    }
}
