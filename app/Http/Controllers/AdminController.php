<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Enums\Role;
use App\Http\Requests\UpdateUserByAdminRequest;
use App\Models\Chat;
use App\Models\Coche;
use App\Models\Mensaje;
use App\Models\User;
use App\Services\ImagenService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

/**
 * Controlador del panel de administración. Las rutas que acaba consumiendo
 * están protegidas por el middleware "admin", así que aquí asumimos que el
 * usuario que llega ya tiene permisos suficientes.
 */
class AdminController extends Controller
{
    public function __construct(protected ImagenService $imagenes) {}

    /* ============================================================
     *  Dashboard
     * ============================================================ */

    /** Panel de inicio con métricas globales y últimos registros. */
    public function dashboard()
    {
        $stats = [
            'users_total' => User::count(),
            'users_admins' => User::where('role', Role::Admin)->count(),
            'coches_total' => Coche::count(),
            'coches_active' => Coche::where('status', ListingStatus::Active)->count(),
            'coches_sold' => Coche::where('status', ListingStatus::Sold)->count(),
            'coches_draft' => Coche::where('status', ListingStatus::Draft)->count(),
            'chats' => Chat::count(),
            'mensajes' => Mensaje::count(),
        ];

        $recentCoches = Coche::with(['user', 'imagenPrincipal'])
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentCoches', 'recentUsers'));
    }

    /* ============================================================
     *  Gestión de usuarios
     * ============================================================ */

    /** Listado paginado de usuarios con buscador por nombre o correo. */
    public function usersIndex(Request $request)
    {
        $q = $request->query('q');

        $users = User::query()
            ->withCount('coches')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    /** Formulario de edición de un usuario concreto desde el panel de admin. */
    public function userEdit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /** Guarda los cambios introducidos por el admin sobre un usuario. */
    public function userUpdate(UpdateUserByAdminRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario actualizado.');
    }

    /** Elimina un usuario, impidiendo que el admin se borre a sí mismo. */
    public function userDestroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('status', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario eliminado.');
    }

    /* ============================================================
     *  Moderación de coches
     * ============================================================ */

    /** Listado paginado de anuncios con búsqueda y filtro por estado. */
    public function cochesIndex(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');

        $coches = Coche::query()
            ->with(['user', 'imagenPrincipal'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                       ->orWhere('brand', 'like', "%{$q}%")
                       ->orWhere('model', 'like', "%{$q}%");
                });
            })
            ->when($status && ListingStatus::tryFrom($status), fn ($qq) => $qq->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.coches.index', [
            'coches' => $coches,
            'q' => $q,
            'status' => $status,
            'statuses' => collect(ListingStatus::cases())
                ->mapWithKeys(fn ($c) => [$c->value => $c->label()]),
        ]);
    }

    /** Cambia el estado de un anuncio (activo, vendido, expirado, borrador). */
    public function cocheUpdateStatus(Request $request, Coche $coche)
    {
        $data = $request->validate([
            'status' => ['required', new Enum(ListingStatus::class)],
        ]);

        $coche->update($data);

        return back()->with('status', 'Estado actualizado a '.$coche->status->label().'.');
    }

    /** Marca o desmarca un anuncio como destacado en la home. */
    public function cocheToggleFeatured(Coche $coche)
    {
        $coche->update(['featured' => ! $coche->featured]);

        return back()->with('status', $coche->featured ? 'Anuncio destacado.' : 'Quitado de destacados.');
    }

    /** Borra un anuncio desde el panel de admin junto con sus imágenes. */
    public function cocheDestroy(Coche $coche)
    {
        $this->imagenes->deleteAllForCoche($coche);
        $coche->delete();

        return redirect()
            ->route('admin.coches.index')
            ->with('status', 'Anuncio eliminado.');
    }
}
