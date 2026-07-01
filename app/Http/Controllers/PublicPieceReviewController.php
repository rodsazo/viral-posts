<?php

namespace App\Http\Controllers;

use App\Enums\ClientReviewStatus;
use App\Models\ContentPiece;
use App\Notifications\PieceReviewedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PublicPieceReviewController extends Controller
{
    /**
     * Respuesta del cliente desde la vista pública: aprobar o pedir cambios (con nota).
     * Sin login: el token de la pieza es el control de acceso, igual que para verla.
     */
    public function __invoke(Request $request, ContentPiece $piece): RedirectResponse
    {
        abort_unless($piece->isPubliclyVisible(), 404);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,changes_requested'],
            'notes' => ['nullable', 'string', 'max:2000', 'required_if:decision,changes_requested'],
        ], [
            'notes.required_if' => 'Cuéntanos qué cambios necesitas.',
        ]);

        $status = ClientReviewStatus::from($data['decision']);

        $piece->update([
            'client_review_status' => $status->value,
            // La nota solo aplica cuando se piden cambios.
            'client_review_notes' => $status === ClientReviewStatus::ChangesRequested ? trim((string) $data['notes']) : null,
            'client_reviewed_at' => now(),
        ]);

        // Avisar al equipo de la marca (en cola; requiere worker + mailer configurado).
        Notification::send($piece->account->users, new PieceReviewedNotification($piece));

        return redirect()
            ->route('piece.public', $piece->public_token)
            ->with('review.flash', $status === ClientReviewStatus::Approved
                ? '¡Gracias! Registramos tu aprobación. 🎉'
                : '¡Gracias! Pasamos tus comentarios al equipo. ✍️');
    }
}
