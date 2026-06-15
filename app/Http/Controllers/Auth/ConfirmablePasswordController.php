<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Vuelve a pedir la contraseña al usuario antes de ejecutar acciones
 * sensibles, aunque ya tenga sesión iniciada.
 */
class ConfirmablePasswordController extends Controller
{
    /** Muestra el formulario de confirmación de contraseña. */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /** Verifica la contraseña introducida y deja constancia en la sesión. */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
