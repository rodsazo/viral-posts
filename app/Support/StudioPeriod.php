<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Period;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Periodo "activo" del Estudio: el que el usuario tiene seleccionado para planificar.
 * Se guarda en sesión por marca. Valores posibles en sesión:
 *  - id de periodo  → ese periodo.
 *  - 'none'         → modo "Sin periodo" (ver/gestionar piezas sin asignar).
 *  - nada           → cae al periodo más reciente (o null si la marca no tiene periodos).
 */
class StudioPeriod
{
    public const NONE = 'none';

    private static function key(Account $account): string
    {
        return "studio.period.{$account->id}";
    }

    /** Periodo activo, o null si el usuario está en modo "Sin periodo" (o no hay periodos). */
    public static function get(Account $account): ?Period
    {
        $value = session(self::key($account));

        if ($value === self::NONE) {
            return null;
        }

        if ($value) {
            $period = $account->periods()->find($value);

            if ($period !== null) {
                return $period;
            }
        }

        return $account->periods()->latest('id')->first();
    }

    public static function id(Account $account): ?int
    {
        return self::get($account)?->id;
    }

    /** ¿El usuario eligió explícitamente el modo "Sin periodo"? */
    public static function isNone(Account $account): bool
    {
        return session(self::key($account)) === self::NONE;
    }

    public static function set(Account $account, int $periodId): void
    {
        session([self::key($account) => $periodId]);
    }

    /** Activa el modo "Sin periodo" (piezas sin asignar). */
    public static function setNone(Account $account): void
    {
        session([self::key($account) => self::NONE]);
    }

    /**
     * Acota una consulta de piezas al periodo activo. Si no hay periodo activo (modo
     * "Sin periodo", o la marca aún no tiene periodos), muestra las piezas sin asignar.
     */
    public static function scopeQuery(Builder $query, Account $account): Builder
    {
        $id = self::id($account);

        return $id === null
            ? $query->whereNull('period_id')
            : $query->where('period_id', $id);
    }
}
