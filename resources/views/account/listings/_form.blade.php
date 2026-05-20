@php
    $fuels = \App\Enums\FuelType::options();
    $transmissions = \App\Enums\Transmission::options();
    $bodies = \App\Enums\BodyType::options();
    $statuses = collect(\App\Enums\ListingStatus::cases())
        ->reject(fn ($c) => $c === \App\Enums\ListingStatus::Expired)
        ->mapWithKeys(fn ($c) => [$c->value => $c->label()]);
    $val = fn ($k, $default = '') => old($k, data_get($listing, $k) instanceof \BackedEnum ? data_get($listing, $k)->value : data_get($listing, $k, $default));
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Información básica</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700">Título</label>
                <input type="text" name="title" value="{{ $val('title') }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Marca</label>
                    <input type="text" name="brand" value="{{ $val('brand') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('brand') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Modelo</label>
                    <input type="text" name="model" value="{{ $val('model') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('model') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="description" rows="6" required
                          class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $val('description') }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Características</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio (€)</label>
                    <input type="number" min="1" name="price" value="{{ $val('price') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Año</label>
                    <input type="number" min="1950" max="{{ date('Y') + 1 }}" name="year" value="{{ $val('year') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('year') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kilómetros</label>
                    <input type="number" min="0" name="mileage_km" value="{{ $val('mileage_km') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('mileage_km') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Color</label>
                    <input type="text" name="color" value="{{ $val('color') }}"
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('color') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Combustible</label>
                    <select name="fuel_type" required class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">—</option>
                        @foreach($fuels as $v => $label)
                            <option value="{{ $v }}" @selected($val('fuel_type') === $v)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('fuel_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cambio</label>
                    <select name="transmission" required class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">—</option>
                        @foreach($transmissions as $v => $label)
                            <option value="{{ $v }}" @selected($val('transmission') === $v)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('transmission') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Carrocería</label>
                    <select name="body_type" required class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">—</option>
                        @foreach($bodies as $v => $label)
                            <option value="{{ $v }}" @selected($val('body_type') === $v)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('body_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Provincia</label>
                <input type="text" name="province" value="{{ $val('province', auth()->user()->province) }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('province') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Imágenes</h2>

            @if(isset($listing) && $listing->exists && $listing->images->count())
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    @foreach($listing->images as $img)
                        <label class="relative block group cursor-pointer">
                            <img src="{{ $img->url }}" class="w-full aspect-[4/3] object-cover rounded border">
                            <input type="checkbox" name="delete_images[]" value="{{ $img->id }}"
                                   class="absolute top-2 left-2">
                            @if($img->is_primary)
                                <span class="absolute bottom-1 right-1 bg-indigo-600 text-white text-[10px] px-1.5 py-0.5 rounded">Principal</span>
                            @endif
                            <span class="block text-xs text-gray-500 mt-1">Marcar para eliminar</span>
                        </label>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Subir nuevas imágenes (máx. 8, hasta 4 MB c/u)</label>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-gray-700">
                @error('images') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @error('images.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <aside class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Publicación</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="status" required class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($statuses as $v => $label)
                        <option value="{{ $v }}" @selected($val('status') === $v)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Marca <em>Activo</em> para que sea visible al público.</p>
                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                {{ $submitLabel ?? 'Guardar' }}
            </button>
        </div>
    </aside>
</div>
