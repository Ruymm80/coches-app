<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Coche;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerfilController extends Controller
{
    /* ============================================================
     *  Dashboard del usuario
     * ============================================================ */

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total' => $user->coches()->count(),
            'active' => $user->coches()->where('status', ListingStatus::Active)->count(),
            'sold' => $user->coches()->where('status', ListingStatus::Sold)->count(),
            'draft' => $user->coches()->where('status', ListingStatus::Draft)->count(),
            'views' => (int) $user->coches()->sum('views_count'),
            'favoritos' => $user->favoritos()->count(),
        ];

        $latest = $user->coches()
            ->with('imagenPrincipal')
            ->latest()
            ->take(5)
            ->get();

        return view('perfil.dashboard', compact('stats', 'latest'));
    }

    /* ============================================================
     *  Datos personales (perfil)
     * ============================================================ */

    public function edit(Request $request): View
    {
        return view('perfil.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Foto de perfil opcional — se almacena como BLOB en la BD.
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $file = $request->file('avatar');
            $user->avatar_data = file_get_contents($file->getRealPath());
            $user->avatar_mime = $file->getMimeType() ?: 'image/jpeg';
        }

        $user->save();

        return redirect()->route('perfil.edit')->with('status', 'profile-updated');
    }

    /** Sirve el binario del avatar de un usuario. */
    public function avatar(User $user)
    {
        $full = User::withAvatarData()->find($user->id);
        abort_if(! $full || ! $full->avatar_data, 404);

        return response($full->avatar_data, 200, [
            'Content-Type'  => $full->avatar_mime ?? 'image/jpeg',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /* ============================================================
     *  Favoritos
     * ============================================================ */

    public function favoritos(Request $request)
    {
        $coches = $request->user()
            ->favoritedCoches()
            ->with('imagenPrincipal')
            ->latest('favoritos.created_at')
            ->paginate(12);

        return view('perfil.favoritos', compact('coches'));
    }

    public function toggleFavorito(Request $request, Coche $coche)
    {
        $user = $request->user();
        $existing = $user->favoritos()->where('coche_id', $coche->id)->first();

        if ($existing) {
            $existing->delete();
            $message = 'Eliminado de favoritos.';
        } else {
            $user->favoritos()->create(['coche_id' => $coche->id]);
            $message = 'Añadido a favoritos.';
        }

        return back()->with('status', $message);
    }
}
