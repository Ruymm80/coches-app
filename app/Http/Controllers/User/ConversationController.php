<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            })
            ->with(['listing.primaryImage', 'buyer', 'seller', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('account.messages.index', compact('conversations'));
    }

    public function show(Conversation $conversation, Request $request)
    {
        Gate::authorize('view', $conversation);

        $user = $request->user();
        $conversation->load(['listing.primaryImage', 'buyer', 'seller', 'messages.sender']);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('account.messages.show', compact('conversation'));
    }

    public function startFromListing(SendMessageRequest $request, Listing $listing)
    {
        $user = $request->user();

        abort_if($user->id === $listing->user_id, 403, 'No puedes contactarte a ti mismo.');

        $conversation = DB::transaction(function () use ($user, $listing, $request) {
            $conversation = Conversation::firstOrCreate(
                [
                    'listing_id' => $listing->id,
                    'buyer_id' => $user->id,
                ],
                [
                    'seller_id' => $listing->user_id,
                ]
            );

            $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => $request->validated('body'),
            ]);

            return $conversation;
        });

        return redirect()
            ->route('account.messages.show', $conversation)
            ->with('status', 'Mensaje enviado al vendedor.');
    }

    public function reply(SendMessageRequest $request, Conversation $conversation)
    {
        Gate::authorize('reply', $conversation);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()->route('account.messages.show', $conversation);
    }
}
