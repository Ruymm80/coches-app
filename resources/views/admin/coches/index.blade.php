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
          <div class="overflow-x-auto">
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
                    @foreach($coches as $coche)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 min-w-[260px]">
                                    <img src="{{ $coche->imagenPrincipal?->url ?? 'https://loremflickr.com/100/75/car?lock='.$coche->id }}"
                                         class="shrink-0 w-16 h-12 object-cover rounded">
                                    <div class="min-w-0">
                                        <a href="{{ route('coches.show', $coche) }}"
                                           class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                            {{ $coche->title }}
                                        </a>
                                        <div class="text-xs text-gray-500 truncate">{{ $coche->brand }} · {{ $coche->year }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $coche->user->name }}</td>
                            <td class="px-4 py-3 text-right"><x-price-format :value="$coche->price" class="font-semibold text-gray-900" /></td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.coches.status', $coche) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($statuses as $v => $label)
                                            <option value="{{ $v }}" @selected($coche->status->value === $v)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.coches.feature', $coche) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-sm {{ $coche->featured ? 'text-amber-600' : 'text-gray-400' }} hover:text-amber-700">
                                        {{ $coche->featured ? '★ Sí' : '☆ No' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <form method="POST" action="{{ route('admin.coches.regenerate-image', $coche) }}" class="inline">
                                        @csrf
                                        <button class="text-sm text-purple-600 hover:text-purple-800 font-medium" title="Descargar una foto nueva para este anuncio">
                                            ↻ Foto
                                        </button>
                                    </form>
                                    <a href="{{ route('coches.edit', $coche) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                    <form method="POST" action="{{ route('admin.coches.destroy', $coche) }}"
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
        </div>

        <div class="mt-6">{{ $coches->links() }}</div>
    </div>
</x-app-layout>
