<?php

use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PublicPieceController;
use App\Livewire\Studio\AudienceHub;
use App\Livewire\Studio\CaptureInbox;
use App\Livewire\Studio\ContentCtaManager;
use App\Livewire\Studio\HookManager;
use App\Livewire\Studio\IdeaGenerator;
use App\Livewire\Studio\IdealFollowerKickstart;
use App\Livewire\Studio\PeriodManager;
use App\Livewire\Studio\PieceComposer;
use App\Livewire\Studio\PieceGenerator;
use App\Livewire\Studio\StudioHome;
use App\Livewire\Studio\StudioKanban;
use App\Livewire\Studio\WinningIdeaManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

// Vista pública (sin login) de una pieza para que el cliente la entienda y valide el guión.
Route::get('/p/{piece:public_token}', PublicPieceController::class)->name('piece.public');

// Estudio de creación a medida (Livewire + Flux), escopado a la marca.
Route::middleware(['auth', 'membership'])->group(function () {
    Route::get('/studio/{account:slug}', StudioHome::class)->name('studio.home');
    Route::get('/studio/{account:slug}/inbox', CaptureInbox::class)->name('studio.inbox');
    Route::get('/studio/{account:slug}/audiencia', AudienceHub::class)->name('studio.audience');
    Route::get('/studio/{account:slug}/kickstart', IdealFollowerKickstart::class)->name('studio.kickstart');
    Route::get('/studio/{account:slug}/kanban', StudioKanban::class)->name('studio.kanban');
    Route::get('/studio/{account:slug}/periodos', PeriodManager::class)->name('studio.periods');
    Route::get('/studio/{account:slug}/ideas-ganadoras', WinningIdeaManager::class)->name('studio.winning-ideas');
    Route::get('/studio/{account:slug}/ideas', IdeaGenerator::class)->name('studio.ideas');
    Route::get('/studio/{account:slug}/generador', PieceGenerator::class)->name('studio.generator');
    Route::get('/studio/{account:slug}/ctas', ContentCtaManager::class)->name('studio.ctas');
    Route::get('/studio/{account:slug}/ganchos', HookManager::class)->name('studio.hooks');
    Route::get('/studio/{account:slug}/piezas', PieceComposer::class)->name('studio.pieces');
});
