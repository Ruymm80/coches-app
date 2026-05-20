<x-app-layout>
    <x-slot name="title">Editar usuario — Admin</x-slot>

    @include('admin._nav')

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Volver a usuarios</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Editar usuario</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Provincia</label>
                    <input type="text" name="province" value="{{ old('province', $user->province) }}"
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Rol</label>
                <select name="role" required class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach(\App\Enums\Role::cases() as $case)
                        <option value="{{ $case->value }}" @selected(old('role', $user->role->value) === $case->value)>
                            {{ $case->label() }}
                        </option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2">
                <button class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
