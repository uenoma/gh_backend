<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\GameSessionPlot;
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
     * ゲームセッションレポート取得（参加者・使用機体・パイロットポイント・Plot）
     */
    public function report(string $id)
    {
        $session = GameSession::with(['user:id,name'])->findOrFail($id);

        $members = $session->members()->get();

        $mobileSuitIds = $members->pluck('pivot.mobile_suit_id')->filter()->unique();
        $mobileSuits = MobileSuit::whereIn('id', $mobileSuitIds)
            ->get(['id', 'ms_number', 'ms_name', 'ms_name_optional', 'ms_icon'])
            ->keyBy('id');

        $plotsByUser = GameSessionPlot::where('game_session_id', $session->id)
            ->get()
            ->groupBy('user_id');

        $membersData = $members->map(function ($member) use ($mobileSuits, $plotsByUser) {
            $userPlots = ($plotsByUser->get($member->id) ?? collect())
                ->mapWithKeys(fn ($p) => [$p->inning => [
                    'plot'   => $p->plot,
                    'damage' => $p->damage,
                ]]);

            return [
                'id'          => $member->id,
                'name'        => $member->name,
                'pilot_point' => $member->pivot->pilot_point,
                'joined_at'   => $member->pivot->joined_at,
                'mobile_suit' => $member->pivot->mobile_suit_id
                    ? $mobileSuits->get($member->pivot->mobile_suit_id)
                    : null,
                'plots'       => $userPlots,
            ];
        });

        return response()->json([
            'id'          => $session->id,
            'name'        => $session->name,
            'description' => $session->description,
            'capacity'    => $session->capacity,
            'user'        => $session->user,
            'members'     => $membersData,
        ]);
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
     * パイロットポイントを更新（要認証・参加者のみ）
     */
    public function updatePilotPoint(Request $request, string $id)
    {
        $session = GameSession::findOrFail($id);

        if (!$session->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'このセッションに参加していません。'], 403);
        }

        $validated = $request->validate([
            'pilot_point' => ['required', 'integer', 'min:0'],
        ]);

        $session->members()->updateExistingPivot($request->user()->id, [
            'pilot_point' => $validated['pilot_point'],
        ]);

        return response()->json($session->load(['user:id,name', 'members:id,name']));
    }

    /**
     * 戦闘マップサイズ取得
     */
    public function getMapSize(string $id)
    {
        $session = GameSession::findOrFail($id);

        return response()->json([
            'map_width'  => $session->map_width,
            'map_height' => $session->map_height,
        ]);
    }

    /**
     * 戦闘マップサイズ更新（要認証・作成者のみ）
     */
    public function updateMapSize(Request $request, string $id)
    {
        $session = GameSession::findOrFail($id);

        if ($session->user_id !== $request->user()->id) {
            return response()->json(['message' => '編集する権限がありません。'], 403);
        }

        $validated = $request->validate([
            'map_width'  => ['sometimes', 'required', 'integer', 'min:1', 'max:99'],
            'map_height' => ['sometimes', 'required', 'integer', 'min:1', 'max:99'],
        ]);

        $session->update($validated);

        return response()->json([
            'map_width'  => $session->map_width,
            'map_height' => $session->map_height,
        ]);
    }

    /**
     * 指定イニングの全参加者分のPlot一覧取得
     */
    public function getInningPlots(string $id, int $inning)
    {
        if ($inning < 0 || $inning > 99) {
            return response()->json(['message' => 'inningは0〜99の範囲で指定してください。'], 422);
        }

        $session = GameSession::findOrFail($id);

        $plots = GameSessionPlot::where('game_session_id', $session->id)
            ->where('inning', $inning)
            ->get();

        return response()->json($plots);
    }

    /**
     * イニングの行動計画・Plotを登録・更新（要認証・参加者のみ）
     */
    public function upsertPlot(Request $request, string $id, int $inning)
    {
        if ($inning < 0 || $inning > 99) {
            return response()->json(['message' => 'inningは0〜99の範囲で指定してください。'], 422);
        }

        $session = GameSession::findOrFail($id);

        if (!$session->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'このセッションに参加していません。'], 403);
        }

        $validated = $request->validate([
            'plot'               => ['sometimes', 'nullable', 'array'],
            'plot.hex'           => ['sometimes', 'nullable', 'string', 'regex:/^\d{4}$/'],
            'plot.direction'     => ['sometimes', 'nullable', 'integer', 'min:1', 'max:6'],
            'plot.altitude'      => ['sometimes', 'nullable', 'integer'],
            'plot.inertia'       => ['sometimes', 'nullable', 'array', 'max:3'],
            'plot.inertia.*'     => ['string', 'regex:/^[0-9u]-\d+$/'],
            'damage'             => ['sometimes', 'nullable', 'array'],
            'damage.*'           => ['array'],
        ]);

        $plot = GameSessionPlot::updateOrCreate(
            [
                'game_session_id' => $session->id,
                'user_id'        => $request->user()->id,
                'inning'         => $inning,
            ],
            array_filter([
                'plot'   => $validated['plot'] ?? null,
                'damage' => $validated['damage'] ?? null,
            ], fn ($v) => $v !== null)
        );

        return response()->json($plot);
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
