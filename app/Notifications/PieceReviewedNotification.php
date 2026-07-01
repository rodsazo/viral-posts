<?php

namespace App\Notifications;

use App\Enums\ClientReviewStatus;
use App\Models\ContentPiece;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa al equipo cuando el cliente responde una pieza desde la vista pública
 * (aprobar o pedir cambios). Se encola para no bloquear la respuesta del cliente.
 */
class PieceReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContentPiece $piece) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $piece = $this->piece;
        $brand = $piece->account?->name ?? 'tu marca';
        $title = $piece->title ?: 'Pieza sin título';
        $approved = $piece->client_review_status === ClientReviewStatus::Approved;

        $url = route('studio.pieces', ['account' => $piece->account, 'piece' => $piece->id]);

        $mail = (new MailMessage)
            ->subject(($approved ? '✅ Aprobada' : '✏️ Cambios solicitados')." · {$title} ({$brand})")
            ->greeting($approved ? '¡Buenas noticias! 🎉' : 'El cliente pidió cambios ✍️')
            ->line($approved
                ? "El cliente **aprobó** la pieza «{$title}» de {$brand}."
                : "El cliente pidió cambios en la pieza «{$title}» de {$brand}.");

        if (! $approved && filled($piece->client_review_notes)) {
            $mail->line('Comentario del cliente:')
                ->line('"'.$piece->client_review_notes.'"');
        }

        return $mail
            ->action('Abrir en el Estudio', $url)
            ->line('Puedes ver el detalle y seguir trabajando desde el Composer.');
    }
}
