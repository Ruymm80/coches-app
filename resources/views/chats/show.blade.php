<x-app-layout>
    <x-slot name="title">Conversación — Carros.net</x-slot>

    @php
        $me = auth()->user();
        $other = $chat->otroParticipante($me);
        $coche = $chat->coche;
        $img = $coche->imagenPrincipal?->url ?? 'https://loremflickr.com/200/150/car,automobile/all?lock=0';
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('chats.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Volver a mensajes</a>

        <div class="mt-3 bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-4">
            <img src="{{ $img }}" class="shrink-0 w-20 h-16 object-cover rounded">
            <div class="flex-1 min-w-0">
                <a href="{{ route('coches.show', $coche) }}"
                   class="font-semibold text-gray-900 hover:text-indigo-600 truncate block">
                    {{ $coche->title }}
                </a>
                <div class="text-sm text-gray-500 truncate">
                    Con <span class="font-medium text-gray-700">{{ $other->name }}</span>
                    @if($other->province) · {{ $other->province }} @endif
                </div>
            </div>
            <x-price-format :value="$coche->price" class="shrink-0 text-lg font-bold text-indigo-600 whitespace-nowrap" />
        </div>

        <div class="mt-4 bg-white border border-gray-200 rounded-lg p-4 space-y-3 max-h-[60vh] overflow-y-auto">
            @forelse($chat->mensajes as $mensaje)
                @php $mine = $mensaje->sender_id === $me->id; @endphp
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] {{ $mine ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900' }} rounded-2xl px-4 py-2 break-words">
                        <div class="text-sm whitespace-pre-line break-words">{{ $mensaje->body }}</div>
                        <div class="mt-1 text-[10px] {{ $mine ? 'text-indigo-200' : 'text-gray-500' }}">
                            {{ $mensaje->created_at->diffForHumans() }}
                            @if($mine && $mensaje->read_at) · leído @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-6">No hay mensajes todavía.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('chats.reply', $chat) }}" class="mt-4 bg-white border border-gray-200 rounded-lg p-4">
            @csrf
            <label for="body" class="block text-sm font-medium text-gray-700">Responder</label>
            <textarea id="body" name="body" rows="3" required
                      class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Escribe tu mensaje..."></textarea>
            @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="mt-2 flex justify-end">
                <button class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Enviar
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
