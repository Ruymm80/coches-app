<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Cambia la contraseña del usuario autenticado desde la sección de perfil.
 * Requiere la contraseña actual para evitar cambios no autorizados si la
 * sesión queda abierta en un dispositivo compartido.
 */
class PasswordController extends Controller
{
    /** Valida la contraseña actual y guarda la nueva en formato hash. */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
