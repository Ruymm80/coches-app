<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Solicita el envío por correo del enlace para restablecer la contraseña
 * cuando el usuario la ha olvidado.
 */
class PasswordResetLinkController extends Controller
{
    /** Muestra el formulario donde el usuario indica su correo. */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Envía el enlace de recuperación al correo introducido si existe en
     * la base de datos, y muestra el mensaje correspondiente al usuario.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Pedimos a Laravel que envíe el enlace. La respuesta indica si se ha
        // enviado correctamente o si ha habido algún problema (correo no encontrado,
        // límite de intentos, etc.), para mostrar el mensaje adecuado.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
