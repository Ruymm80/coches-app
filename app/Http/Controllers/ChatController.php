<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnviarMensajeRequest;
use App\Models\Chat;
use App\Models\Coche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $chats = Chat::query()
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            })
            ->with(['coche.imagenPrincipal', 'buyer', 'seller', 'ultimoMensaje'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('chats.index', compact('chats'));
    }

    public function show(Chat $chat, Request $request)
    {
        Gate::authorize('view', $chat);

        $user = $request->user();
        $chat->load(['coche.imagenPrincipal', 'buyer', 'seller', 'mensajes.sender']);

        $chat->mensajes()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('chats.show', compact('chat'));
    }

    public function start(EnviarMensajeRequest $request, Coche $coche)
    {
        $user = $request->user();

        abort_if($user->id === $coche->user_id, 403, 'No puedes contactarte a ti mismo.');

        $chat = DB::transaction(function () use ($user, $coche, $request) {
            $chat = Chat::firstOrCreate(
                [
                    'coche_id' => $coche->id,
                    'buyer_id' => $user->id,
                ],
                [
                    'seller_id' => $coche->user_id,
                ]
            );

            $chat->mensajes()->create([
                'sender_id' => $user->id,
                'body' => $request->validated('body'),
            ]);

            return $chat;
        });

        return redirect()
            ->route('chats.show', $chat)
            ->with('status', 'Mensaje enviado al vendedor.');
    }

    public function reply(EnviarMensajeRequest $request, Chat $chat)
    {
        Gate::authorize('reply', $chat);

        $chat->mensajes()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()->route('chats.show', $chat);
    }
}
