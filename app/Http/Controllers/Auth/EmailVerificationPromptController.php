<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pantalla intermedia tras el registro: pide al usuario que verifique su
 * correo haciendo clic en el enlace recibido por email.
 */
class EmailVerificationPromptController extends Controller
{
    /** Si el correo ya está verificado redirige al dashboard, si no muestra la pantalla. */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
