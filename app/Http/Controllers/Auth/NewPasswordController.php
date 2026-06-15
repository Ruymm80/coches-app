<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Procesa el formulario al que se llega desde el enlace de recuperación
 * de contraseña enviado por correo.
 */
class NewPasswordController extends Controller
{
    /** Muestra la vista del formulario para establecer una contraseña nueva. */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Cambia la contraseña del usuario si el token recibido por correo es válido.
     * Si lo es, se actualiza el hash y se rota el remember_token para invalidar
     * sesiones persistentes anteriores; en caso de error se devuelve el motivo.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Intentamos restablecer la contraseña usando el broker de Laravel.
        // El callback solo se ejecuta si el token es válido y no ha caducado.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Si fue bien redirigimos a la pantalla de login con un aviso;
        // si falló mostramos el error junto al campo de correo.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
