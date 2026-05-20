@props([
    'filters' => [],
    'provinces' => collect(),
    'sorts' => [],
    'action' => null,
])

@php
    $fuels = \App\Enums\FuelType::options();
    $transmissions = \App\Enums\Transmission::options();
    $bodies = \App\Enums\BodyType::options();
    $val = fn ($k) => $filters[$k] ?? '';
@endphp

<form method="GET" action="{{ $action ?? route('listings.index') }}"
      class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $val('q') }}"
               placeholder="Marca, modelo, palabra clave..."
               class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Marca</label>
            <input type="text" name="brand" value="{{ $val('brand') }}"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Modelo</label>
            <input type="text" name="model" value="{{ $val('model') }}"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Precio min (€)</label>
            <input type="number" min="0" name="price_min" value="{{ $val('price_min') }}"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Precio max (€)</label>
            <input type="number" min="0" name="price_max" value="{{ $val('price_max') }}"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Año min</label>
            <input type="number" min="1950" max="{{ date('Y') }}" name="year_min" value="{{ $val('year_min') }}"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Año max</label>
            <input type="number" min="1950" max="{{ date('Y') }}" name="year_max" value="{{ $val('year_max') }}"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">Km máx</label>
        <input type="number" min="0" name="km_max" value="{{ $val('km_max') }}"
               class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">Combustible</label>
        <select name="fuel" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Cualquiera</option>
            @foreach($fuels as $v => $label)
                <option value="{{ $v }}" @selected($val('fuel') === $v)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">Cambio</label>
        <select name="transmission" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Cualquiera</option>
            @foreach($transmissions as $v => $label)
                <option value="{{ $v }}" @selected($val('transmission') === $v)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">Carrocería</label>
        <select name="body" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Cualquiera</option>
            @foreach($bodies as $v => $label)
                <option value="{{ $v }}" @selected($val('body') === $v)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">Provincia</label>
        <select name="province" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todas</option>
            @foreach($provinces as $province)
                <option value="{{ $province }}" @selected($val('province') === $province)>{{ $province }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">Ordenar por</label>
        <select name="sort" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($sorts as $v => $label)
                <option value="{{ $v }}" @selected($val('sort') === $v)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex gap-2 pt-2">
        <button type="submit"
                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
            Buscar
        </button>
        <a href="{{ route('listings.index') }}"
           class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Limpiar
        </a>
    </div>
</form>
