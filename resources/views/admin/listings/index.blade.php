<x-app-layout>
    <x-slot name="title">Anuncios — Admin</x-slot>

    @include('admin._nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <h1 class="text-2xl font-bold text-gray-900">Anuncios</h1>

            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por título, marca o modelo"
                       class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select name="status" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Cualquier estado</option>
                    @foreach($statuses as $v => $label)
                        <option value="{{ $v }}" @selected($status === $v)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Filtrar
                </button>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Anuncio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Vendedor</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Destacado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($listings as $listing)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $listing->primaryImage?->url ?? 'https://loremflickr.com/100/75/car,automobile/all?lock=0' }}"
                                         class="w-16 h-12 object-cover rounded">
                                    <div class="min-w-0">
                                        <a href="{{ route('listings.show', $listing) }}"
                                           class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                            {{ $listing->title }}
                                        </a>
                                        <div class="text-xs text-gray-500">{{ $listing->brand }} · {{ $listing->year }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $listing->user->name }}</td>
                            <td class="px-4 py-3 text-right"><x-price-format :value="$listing->price" class="font-semibold text-gray-900" /></td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.listings.status', $listing) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($statuses as $v => $label)
                                            <option value="{{ $v }}" @selected($listing->status->value === $v)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.listings.feature', $listing) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-sm {{ $listing->featured ? 'text-amber-600' : 'text-gray-400' }} hover:text-amber-700">
                                        {{ $listing->featured ? '★ Sí' : '☆ No' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('account.listings.edit', $listing) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                    <form method="POST" action="{{ route('admin.listings.destroy', $listing) }}"
                                          onsubmit="return confirm('¿Eliminar este anuncio?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-sm text-red-600 hover:text-red-800 font-medium">Borrar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $listings->links() }}</div>
    </div>
</x-app-layout>
