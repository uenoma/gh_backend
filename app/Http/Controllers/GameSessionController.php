<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\MobileSuit;
use Illuminate\Http\Request;

class GameSessionController extends Controller
{
    /**
     * ゲームセッション一覧取得
     */
    public function index()
    {
        $sessions = GameSession::with(['user:id,name', 'members:id,name'])->latest()->get();
        return response()->json($sessions);
    }

    /**
     * ゲームセッション詳細取得
     */
    public function show(string $id)
    {
        $session = GameSession::with(['user:id,name', 'members:id,name'])->findOrFail($id);
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

        return response()->json($session->load(['user:id,name', 'members:id,name']), 201);
    }

    /**
     * ゲームセッション編集（要認証・自分のセッションのみ）
     */
    public function update(Request $request, string $id)
    {
        $session = GameSession::findOrFail($id);

        if ($session->user_id !== $request->user()->id) {
            return response()->json(['message' => '編集する権限がありません。'], 403);
        }

        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity'    => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        $session->update($validated);

        return response()->json($session->load(['user:id,name', 'members:id,name']));
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

    /**
     * ゲームセッションへ参加（要認証）
     */
    public function join(Request $request, string $id)
    {
        $session = GameSession::findOrFail($id);

        if ($session->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => '既に参加しています。'], 409);
        }

        if ($session->members()->count() >= $session->capacity) {
            return response()->json(['message' => '定員に達しています。'], 409);
        }

        $session->members()->attach($request->user()->id);

        return response()->json($session->load(['user:id,name', 'members:id,name']));
    }

    /**
     * 使用機体を選択（要認証・参加者のみ）
     */
    public function selectMobileSuit(Request $request, string $id)
    {
        $session = GameSession::findOrFail($id);

        if (!$session->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'このセッションに参加していません。'], 403);
        }

        $validated = $request->validate([
            'mobile_suit_id' => ['nullable', 'integer', 'exists:mobile_suits,id'],
        ]);

        $session->members()->updateExistingPivot($request->user()->id, [
            'mobile_suit_id' => $validated['mobile_suit_id'] ?? null,
        ]);

        return response()->json($session->load(['user:id,name', 'members:id,name']));
    }

    /**
     * ゲームセッションから離脱（要認証）
     */
    public function leave(Request $request, string $id)
    {
        $session = GameSession::findOrFail($id);

        if (!$session->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => '参加していません。'], 409);
        }

        $session->members()->detach($request->user()->id);

        return response()->json(['message' => 'ゲームセッションから離脱しました。']);
    }
}
