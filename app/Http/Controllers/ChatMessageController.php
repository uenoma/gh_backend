<?php

namespace App\Http\Controllers;

use App\Models\ChatChannel;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    /**
     * チャンネルのメッセージ一覧取得（要認証・メンバーのみ）
     * 最新順でカーソルページネーション
     */
    public function index(Request $request, string $channelId)
    {
        $channel = ChatChannel::findOrFail($channelId);

        if (!$channel->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'チャンネルに参加していません。'], 403);
        }

        $messages = $channel->messages()
            ->with('user:id,name')
            ->latest()
            ->cursorPaginate(50);

        // メッセージ取得時に既読を更新
        $channel->members()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->json($messages);
    }

    /**
     * メッセージ投稿（要認証・メンバーのみ）
     */
    public function store(Request $request, string $channelId)
    {
        $channel = ChatChannel::findOrFail($channelId);

        if (!$channel->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'チャンネルに参加していません。'], 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $channel->messages()->create([
            'user_id' => $request->user()->id,
            'body'    => $validated['body'],
        ]);

        return response()->json($message->load('user:id,name'), 201);
    }

    /**
     * メッセージ削除（要認証・投稿者のみ）
     */
    public function destroy(Request $request, string $channelId, string $messageId)
    {
        $channel = ChatChannel::findOrFail($channelId);

        $message = $channel->messages()->findOrFail($messageId);

        if ($message->user_id !== $request->user()->id) {
            return response()->json(['message' => '削除する権限がありません。'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'メッセージを削除しました。']);
    }
}
