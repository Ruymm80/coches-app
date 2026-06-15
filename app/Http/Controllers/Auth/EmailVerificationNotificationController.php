<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Reenvía el correo de verificación cuando el usuario lo solicita
 * (por ejemplo si no le ha llegado el original).
 */
class EmailVerificationNotificationController extends Controller
{
    /** Envía un correo nuevo con el enlace de verificación. */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
