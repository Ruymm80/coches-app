<x-app-layout>
    <x-slot name="title">Mensajes — Coches.app</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Mensajes</h1>

        @if($conversations->count())
            <div class="bg-white border border-gray-200 rounded-lg divide-y">
                @foreach($conversations as $conversation)
                    @php
                        $other = $conversation->otherParticipant(auth()->user());
                        $unread = $conversation->unreadCountFor(auth()->user());
                        $last = $conversation->latestMessage;
                        $img = $conversation->listing->primaryImage?->url ?? 'https://loremflickr.com/120/90/car,automobile/all?lock=0';
                        $role = auth()->id() === $conversation->seller_id ? 'Comprador' : 'Vendedor';
                    @endphp
                    <a href="{{ route('account.messages.show', $conversation) }}"
                       class="flex items-center gap-4 p-4 hover:bg-gray-50 {{ $unread ? 'bg-indigo-50/40' : '' }}">
                        <img src="{{ $img }}" class="w-16 h-12 object-cover rounded">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-medium text-gray-900 truncate">
                                    {{ $conversation->listing->title }}
                                </div>
                                <div class="text-xs text-gray-500 shrink-0">
                                    {{ optional($conversation->last_message_at)->diffForHumans() }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 truncate">
                                <span class="text-gray-500">{{ $role }}: {{ $other->name }} ·</span>
                                {{ $last?->body }}
                            </div>
                        </div>

                        @if($unread)
                            <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-2 text-xs font-bold rounded-full bg-rose-500 text-white">
                                {{ $unread }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $conversations->links() }}</div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg p-10 text-center">
                <p class="text-gray-700">Aún no tienes conversaciones.</p>
                <a href="{{ route('listings.index') }}"
                   class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Explorar coches
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
