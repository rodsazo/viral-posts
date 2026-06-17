<?php

use App\Http\Controllers\InvitationController;
use App\Livewire\Studio\CaptureInbox;
use App\Livewire\Studio\PieceComposer;
use App\Livewire\Studio\StudioHome;
use App\Livewire\Studio\StudioKanban;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

// Estudio de creación a medida (Livewire + Flux), escopado a la marca.
Route::middleware(['auth', 'membership'])->group(function () {
    Route::get('/studio/{account:slug}', StudioHome::class)->name('studio.home');
    Route::get('/studio/{account:slug}/inbox', CaptureInbox::class)->name('studio.inbox');
    Route::get('/studio/{account:slug}/kanban', StudioKanban::class)->name('studio.kanban');
    Route::get('/studio/{account:slug}/piezas', PieceComposer::class)->name('studio.pieces');
});
