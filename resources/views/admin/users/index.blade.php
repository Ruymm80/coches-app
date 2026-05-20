<x-app-layout>
    <x-slot name="title">Usuarios — Admin</x-slot>

    @include('admin._nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>

            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o email"
                       class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button class="px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Buscar
                </button>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rol</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Anuncios</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Alta</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded
                                    {{ $user->role->value === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $user->listings_count }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('¿Eliminar este usuario y todos sus anuncios?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-sm text-red-600 hover:text-red-800 font-medium">Borrar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</x-app-layout>
