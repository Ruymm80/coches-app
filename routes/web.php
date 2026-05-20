<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\ConversationController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\ListingController as UserListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/coches', [ListingController::class, 'index'])->name('listings.index');
Route::get('/coches/{listing:slug}', [ListingController::class, 'show'])->name('listings.show');

Route::get('/dashboard', fn () => redirect()->route('account.dashboard'))
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->prefix('mi-cuenta')->name('account.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('anuncios', [UserListingController::class, 'index'])->name('listings.index');
    Route::get('anuncios/crear', [UserListingController::class, 'create'])->name('listings.create');
    Route::post('anuncios', [UserListingController::class, 'store'])->name('listings.store');
    Route::get('anuncios/{listing:slug}/editar', [UserListingController::class, 'edit'])->name('listings.edit');
    Route::put('anuncios/{listing:slug}', [UserListingController::class, 'update'])->name('listings.update');
    Route::delete('anuncios/{listing:slug}', [UserListingController::class, 'destroy'])->name('listings.destroy');
    Route::patch('anuncios/{listing:slug}/marcar-vendido', [UserListingController::class, 'markSold'])->name('listings.mark-sold');

    Route::get('favoritos', [FavoriteController::class, 'index'])->name('favorites.index');

    Route::get('mensajes', [ConversationController::class, 'index'])->name('messages.index');
    Route::get('mensajes/{conversation}', [ConversationController::class, 'show'])->name('messages.show');
    Route::post('mensajes/{conversation}/responder', [ConversationController::class, 'reply'])->name('messages.reply');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/anuncios/{listing:slug}/favorito', [FavoriteController::class, 'toggle'])
        ->name('listings.favorite');

    Route::post('/anuncios/{listing:slug}/contactar', [ConversationController::class, 'startFromListing'])
        ->name('listings.contact');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('usuarios', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('usuarios/{user}/editar', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('usuarios/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('usuarios/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('anuncios', [AdminListingController::class, 'index'])->name('listings.index');
    Route::patch('anuncios/{listing:slug}/estado', [AdminListingController::class, 'updateStatus'])->name('listings.status');
    Route::patch('anuncios/{listing:slug}/destacar', [AdminListingController::class, 'toggleFeatured'])->name('listings.feature');
    Route::delete('anuncios/{listing:slug}', [AdminListingController::class, 'destroy'])->name('listings.destroy');
});

require __DIR__.'/auth.php';
