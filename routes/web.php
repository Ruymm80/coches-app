<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CocheController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

/* ============================================================
 *  Público
 * ============================================================ */

Route::get('/', [CocheController::class, 'home'])->name('home');
Route::get('/coches', [CocheController::class, 'index'])->name('coches.index');
Route::get('/coches/{coche:slug}', [CocheController::class, 'show'])->name('coches.show');

// Alias para Breeze (algunos controllers internos lo usan)
Route::get('/dashboard', fn () => redirect()->route('perfil.dashboard'))
    ->middleware('auth')
    ->name('dashboard');

/* ============================================================
 *  Autenticación
 * ============================================================ */

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/* ============================================================
 *  Área de usuario (perfil + coches propios + chats + favoritos)
 * ============================================================ */

Route::middleware('auth')->group(function () {
    // Perfil / dashboard
    Route::get('/mi-cuenta', [PerfilController::class, 'dashboard'])->name('perfil.dashboard');
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::patch('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::delete('/perfil', [PerfilController::class, 'destroy'])->name('perfil.destroy');

    // Coches propios
    Route::get('/mi-cuenta/coches', [CocheController::class, 'mine'])->name('coches.mine');
    Route::get('/mi-cuenta/coches/crear', [CocheController::class, 'create'])->name('coches.create');
    Route::post('/mi-cuenta/coches', [CocheController::class, 'store'])->name('coches.store');
    Route::get('/mi-cuenta/coches/{coche:slug}/editar', [CocheController::class, 'edit'])->name('coches.edit');
    Route::put('/mi-cuenta/coches/{coche:slug}', [CocheController::class, 'update'])->name('coches.update');
    Route::delete('/mi-cuenta/coches/{coche:slug}', [CocheController::class, 'destroy'])->name('coches.destroy');
    Route::patch('/mi-cuenta/coches/{coche:slug}/marcar-vendido', [CocheController::class, 'markSold'])->name('coches.mark-sold');

    // Favoritos
    Route::get('/mi-cuenta/favoritos', [PerfilController::class, 'favoritos'])->name('perfil.favoritos');
    Route::post('/coches/{coche:slug}/favorito', [PerfilController::class, 'toggleFavorito'])->name('coches.favorite');

    // Chats
    Route::get('/mi-cuenta/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/mi-cuenta/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/mi-cuenta/chats/{chat}/responder', [ChatController::class, 'reply'])->name('chats.reply');
    Route::post('/coches/{coche:slug}/contactar', [ChatController::class, 'start'])->name('coches.contact');
});

/* ============================================================
 *  Panel admin
 * ============================================================ */

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Usuarios
    Route::get('usuarios', [AdminController::class, 'usersIndex'])->name('users.index');
    Route::get('usuarios/{user}/editar', [AdminController::class, 'userEdit'])->name('users.edit');
    Route::put('usuarios/{user}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::delete('usuarios/{user}', [AdminController::class, 'userDestroy'])->name('users.destroy');

    // Coches
    Route::get('coches', [AdminController::class, 'cochesIndex'])->name('coches.index');
    Route::patch('coches/{coche:slug}/estado', [AdminController::class, 'cocheUpdateStatus'])->name('coches.status');
    Route::patch('coches/{coche:slug}/destacar', [AdminController::class, 'cocheToggleFeatured'])->name('coches.feature');
    Route::delete('coches/{coche:slug}', [AdminController::class, 'cocheDestroy'])->name('coches.destroy');
});

require __DIR__.'/auth.php';
