<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\ContentPiece;
use Illuminate\Contracts\View\View;

class PublicBrandController extends Controller
{
    /**
     * Tablero público (sin login) de la marca para el cliente: muestra el ÚLTIMO periodo
     * "Publicado" y, si lo hay, un kanban de solo lectura con las piezas de ese periodo que
     * estén "de Lista para grabación en adelante". Cada tarjeta enlaza a la vista pública de
     * la pieza. Acceso por token inadivinable (route-model binding por `public_token`).
     */
    public function __invoke(Account $account): View
    {
        $period = $account->latestPublishedPeriod();

        $columns = [];

        if ($period !== null) {
            $byStatus = $account->contentPieces()
                ->where('period_id', $period->id)
                ->whereIn('status', collect(ContentStatus::readyForClientCases())->map->value)
                ->with('winningIdea')
                ->orderByDesc('updated_at')
                ->get()
                ->groupBy(fn (ContentPiece $piece) => $piece->status->value);

            foreach (ContentStatus::readyForClientCases() as $status) {
                $columns[] = [
                    'status' => $status,
                    'pieces' => $byStatus->get($status->value, collect()),
                ];
            }
        }

        return view('public.brand', [
            'brand' => $account,
            'period' => $period,
            'columns' => $columns,
        ]);
    }
}
