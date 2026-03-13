<?php

namespace App\Http\Controllers;

use App\Models\ChatChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatChannelController extends Controller
{
    /**
     * チャンネル一覧取得
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $channels = ChatChannel::with('creator:id,name')->latest()->get();

        // 参加チャンネルごとの未読件数を1クエリで取得
        $unreadCounts = DB::table('chat_messages')
            ->join('chat_channel_members', 'chat_messages.chat_channel_id', '=', 'chat_channel_members.chat_channel_id')
            ->where('chat_channel_members.user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('chat_channel_members.last_read_at')
                  ->orWhereColumn('chat_messages.created_at', '>', 'chat_channel_members.last_read_at');
            })
            ->select('chat_messages.chat_channel_id', DB::raw('COUNT(*) as count'))
            ->groupBy('chat_messages.chat_channel_id')
            ->pluck('count', 'chat_channel_id');

        $channels->each(function ($channel) use ($unreadCounts) {
            $channel->unread_count = $unreadCounts->get($channel->id, 0);
        });

        return response()->json($channels);
    }

    /**
     * チャンネル詳細取得
     */
    public function show(Request $request, string $id)
    {
        $channel = ChatChannel::with(['creator:id,name', 'members:id,name'])->findOrFail($id);

        $membership = DB::table('chat_channel_members')
            ->where('chat_channel_id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        $channel->unread_count = $membership
            ? $channel->messages()
                ->when($membership->last_read_at, fn($q) => $q->where('created_at', '>', $membership->last_read_at))
                ->count()
            : 0;

        return response()->json($channel);
    }

    /**
     * チャンネル作成（要認証）
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:chat_channels,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $channel = $request->user()->chatChannels()->create($validated);

        return response()->json($channel->load('creator:id,name'), 201);
    }

    /**
     * チャンネル更新（要認証・作成者のみ・システムチャンネル不可）
     */
    public function update(Request $request, string $id)
    {
        $channel = ChatChannel::findOrFail($id);

        if ($channel->is_system) {
            return response()->json(['message' => 'システムチャンネルは変更できません。'], 403);
        }

        if ($channel->user_id !== $request->user()->id) {
            return response()->json(['message' => '編集する権限がありません。'], 403);
        }

        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:100', 'unique:chat_channels,name,' . $channel->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $channel->update($validated);

        return response()->json($channel->load('creator:id,name'));
    }

    /**
     * チャンネル削除（要認証・作成者のみ・システムチャンネル不可）
     */
    public function destroy(Request $request, string $id)
    {
        $channel = ChatChannel::findOrFail($id);

        if ($channel->is_system) {
            return response()->json(['message' => 'システムチャンネルは削除できません。'], 403);
        }

        if ($channel->user_id !== $request->user()->id) {
            return response()->json(['message' => '削除する権限がありません。'], 403);
        }

        $channel->delete();

        return response()->json(['message' => 'チャンネルを削除しました。']);
    }

    /**
     * チャンネルへ参加（要認証）
     */
    public function join(Request $request, string $id)
    {
        $channel = ChatChannel::findOrFail($id);

        if ($channel->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => '既に参加しています。'], 409);
        }

        $channel->members()->attach($request->user()->id, ['last_read_at' => now()]);

        return response()->json($channel->load(['creator:id,name', 'members:id,name']));
    }

    /**
     * チャンネルから退出（要認証）
     */
    public function leave(Request $request, string $id)
    {
        $channel = ChatChannel::findOrFail($id);

        if (!$channel->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => '参加していません。'], 409);
        }

        $channel->members()->detach($request->user()->id);

        return response()->json(['message' => 'チャンネルから退出しました。']);
    }

    /**
     * 既読マーク（要認証・メンバーのみ）
     */
    public function markAsRead(Request $request, string $id)
    {
        $channel = ChatChannel::findOrFail($id);

        if (!$channel->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'チャンネルに参加していません。'], 403);
        }

        $channel->members()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->json(['message' => '既読にしました。']);
    }
}
