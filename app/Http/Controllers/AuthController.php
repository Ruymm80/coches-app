<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controlador unificado de autenticación: login, registro y logout.
 * Reemplaza a los controladores que genera Breeze por defecto.
 */
class AuthController extends Controller
{
    /* ============================================================
     *  Login
     * ============================================================ */

    /** Muestra el formulario de inicio de sesión. */
    public function loginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión y regenera la sesión para evitar
     * ataques de fijación.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('perfil.dashboard', absolute: false));
    }

    /* ============================================================
     *  Registro
     * ============================================================ */

    /** Muestra el formulario de registro de nuevo usuario. */
    public function registerForm(): View
    {
        return view('auth.register');
    }

    /**
     * Crea la cuenta nueva con rol de usuario normal y la deja autenticada.
     *
     * @throws ValidationException
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'province' => ['nullable', 'string', 'max:60'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'province' => $request->province,
            'role' => Role::User,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('perfil.dashboard', absolute: false));
    }

    /* ============================================================
     *  Logout
     * ============================================================ */

    /** Cierra la sesión actual e invalida el token CSRF asociado. */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
