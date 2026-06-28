<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Period;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Periodo "activo" del Estudio: el que el usuario tiene seleccionado para planificar.
 * Se guarda en sesión por marca. Si no hay uno válido en sesión, cae al periodo más
 * reciente de la marca (o null si la marca aún no tiene periodos).
 */
class StudioPeriod
{
    private static function key(Account $account): string
    {
        return "studio.period.{$account->id}";
    }

    public static function get(Account $account): ?Period
    {
        $id = session(self::key($account));

        $period = $id ? $account->periods()->find($id) : null;

        return $period ?? $account->periods()->latest('id')->first();
    }

    public static function id(Account $account): ?int
    {
        return self::get($account)?->id;
    }

    public static function set(Account $account, int $periodId): void
    {
        session([self::key($account) => $periodId]);
    }

    /**
     * Acota una consulta de piezas al periodo activo. Si no hay periodo activo (la marca
     * aún no tiene periodos), muestra las piezas "sin periodo".
     */
    public static function scopeQuery(Builder $query, Account $account): Builder
    {
        $id = self::id($account);

        return $id === null
            ? $query->whereNull('period_id')
            : $query->where('period_id', $id);
    }
}
