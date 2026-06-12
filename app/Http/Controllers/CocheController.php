<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Http\Requests\StoreCocheRequest;
use App\Http\Requests\UpdateCocheRequest;
use App\Models\Coche;
use App\Services\ImagenService;
use App\Support\CocheFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CocheController extends Controller
{
    public function __construct(protected ImagenService $imagenes) {}

    /* ============================================================
     *  Público
     * ============================================================ */

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

    protected function canPreview(Coche $coche): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->id === $coche->user_id);
    }

    /* ============================================================
     *  CRUD del usuario sobre sus propios coches
     * ============================================================ */

    public function mine(Request $request)
    {
        $coches = $request->user()
            ->coches()
            ->with('imagenPrincipal')
            ->latest()
            ->paginate(10);

        return view('coches.mine', compact('coches'));
    }

    public function create()
    {
        return view('coches.create', [
            'coche' => new Coche(['status' => ListingStatus::Draft->value]),
        ]);
    }

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

    public function edit(Coche $coche)
    {
        Gate::authorize('update', $coche);

        $coche->loadMissing('imagenes');

        return view('coches.edit', compact('coche'));
    }

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

    public function destroy(Coche $coche)
    {
        Gate::authorize('delete', $coche);

        $this->imagenes->deleteAllForCoche($coche);
        $coche->delete();

        return redirect()
            ->route('coches.mine')
            ->with('status', 'Anuncio eliminado.');
    }

    public function markSold(Coche $coche)
    {
        Gate::authorize('update', $coche);

        $coche->update(['status' => ListingStatus::Sold]);

        return back()->with('status', 'Anuncio marcado como vendido.');
    }
}
