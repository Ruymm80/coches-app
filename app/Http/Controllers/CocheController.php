<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Http\Requests\StoreCocheRequest;
use App\Http\Requests\UpdateCocheRequest;
use App\Models\Coche;
use App\Models\Imagen;
use App\Services\ImagenService;
use App\Support\CocheFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controlador de coches: zona pública (home, listado, ficha) y zona
 * privada del usuario (sus propios anuncios). La gestión de la galería
 * de imágenes se delega en ImagenService para mantener este controlador
 * centrado en orquestar requests y vistas.
 */
class CocheController extends Controller
{
    public function __construct(protected ImagenService $imagenes) {}

    /* ============================================================
     *  Público
     * ============================================================ */

    /** Página de inicio: muestra anuncios destacados y los más recientes. */
    public function home()
    {
        $featured = Coche::active()
            ->with('imagenPrincipal')
            ->where('featured', true)
            ->latest()
            ->take(6)
            ->get();

        $recent = Coche::active()
            ->with('imagenPrincipal')
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('featured', 'recent'));
    }

    /** Listado público de coches con filtros y paginación. */
    public function index(Request $request, CocheFilter $filter)
    {
        $coches = $filter->apply(
            Coche::active()->with('imagenPrincipal')
        )->paginate(12)->withQueryString();

        $provinces = Coche::active()
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        return view('coches.index', [
            'coches' => $coches,
            'provinces' => $provinces,
            'filters' => $request->query(),
            'sorts' => CocheFilter::SORTS,
        ]);
    }

    /**
     * Ficha pública de un anuncio. Si no está activo, solo el dueño o un admin
     * pueden verlo (para previsualizar borradores).
     */
    public function show(Coche $coche)
    {
        abort_unless($coche->status->value === 'active' || $this->canPreview($coche), 404);

        $coche->loadMissing(['imagenes', 'user']);
        $coche->increment('views_count');

        $similares = Coche::active()
            ->with('imagenPrincipal')
            ->where('id', '!=', $coche->id)
            ->where('brand', $coche->brand)
            ->latest()
            ->take(4)
            ->get();

        return view('coches.show', compact('coche', 'similares'));
    }

    /** Permite ver el anuncio incluso si no está activo, al dueño o a un administrador. */
    protected function canPreview(Coche $coche): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->id === $coche->user_id);
    }

    /**
     * Endpoint público que sirve el binario de una imagen almacenada como BLOB
     * en la base de datos. Aplica cache de larga duración porque las imágenes
     * son inmutables una vez subidas.
     */
    public function imagen(Imagen $imagen)
    {
        $full = Imagen::withData()->find($imagen->id);
        abort_if(! $full || ! $full->data, 404);

        return response($full->data, 200, [
            'Content-Type'  => $full->mime_type ?? 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /* ============================================================
     *  CRUD del usuario sobre sus propios coches
     * ============================================================ */

    /** Listado paginado de anuncios publicados por el usuario autenticado. */
    public function mine(Request $request)
    {
        $coches = $request->user()
            ->coches()
            ->with('imagenPrincipal')
            ->latest()
            ->paginate(10);

        return view('coches.mine', compact('coches'));
    }

    /** Formulario para publicar un anuncio nuevo (inicialmente como borrador). */
    public function create()
    {
        return view('coches.create', [
            'coche' => new Coche(['status' => ListingStatus::Draft->value]),
        ]);
    }

    /** Guarda el anuncio nuevo y sube sus imágenes (si hay). */
    public function store(StoreCocheRequest $request)
    {
        $data = $request->validated();
        $files = $request->file('images', []);
        unset($data['images']);

        $coche = $request->user()->coches()->create($data);

        if (! empty($files)) {
            $this->imagenes->storeFor($coche, $files);
        }

        return redirect()
            ->route('coches.mine')
            ->with('status', 'Anuncio creado correctamente.');
    }

    /** Formulario de edición de un anuncio propio (protegido por la policy). */
    public function edit(Coche $coche)
    {
        Gate::authorize('update', $coche);

        $coche->loadMissing('imagenes');

        return view('coches.edit', compact('coche'));
    }

    /**
     * Actualiza un anuncio existente: aplica cambios en los campos, borra
     * las imágenes marcadas y añade las nuevas que se hayan subido.
     */
    public function update(UpdateCocheRequest $request, Coche $coche)
    {
        $data = $request->validated();
        $files = $request->file('images', []);
        $deleteIds = $data['delete_images'] ?? [];
        unset($data['images'], $data['delete_images']);

        $coche->update($data);

        if (! empty($deleteIds)) {
            $this->imagenes->deleteForCoche($coche, $deleteIds);
        }

        if (! empty($files)) {
            $this->imagenes->storeFor($coche, $files);
        }

        return redirect()
            ->route('coches.edit', $coche)
            ->with('status', 'Anuncio actualizado.');
    }

    /** Borra un anuncio propio y todas sus imágenes. */
    public function destroy(Coche $coche)
    {
        Gate::authorize('delete', $coche);

        $this->imagenes->deleteAllForCoche($coche);
        $coche->delete();

        return redirect()
            ->route('coches.mine')
            ->with('status', 'Anuncio eliminado.');
    }

    /** Marca el anuncio como vendido sin necesidad de borrarlo. */
    public function markSold(Coche $coche)
    {
        Gate::authorize('update', $coche);

        $coche->update(['status' => ListingStatus::Sold]);

        return back()->with('status', 'Anuncio marcado como vendido.');
    }
}
