<?php

namespace App\Http\Controllers;

use App\Models\ContentPiece;
use Illuminate\Contracts\View\View;

class PublicPieceController extends Controller
{
    /**
     * Página pública (sin login) de una pieza de contenido: pensada para que un
     * cliente externo entienda la pieza y revise/valide el guión. El acceso es por
     * token inadivinable (route-model binding por `public_token`).
     */
    public function __invoke(ContentPiece $piece): View
    {
        $piece->load(['account', 'idealFollower', 'winningIdea', 'period']);

        // Solo visible si la pieza está lista para el cliente (estado >= Lista para
        // grabación) Y su periodo está Publicado. En cualquier otro caso, 404.
        abort_unless($piece->isPubliclyVisible(), 404);

        return view('public.piece', [
            'piece' => $piece,
            'idea' => $piece->winningIdea,
            'follower' => $piece->idealFollower,
            'brand' => $piece->account,
        ]);
    }
}
