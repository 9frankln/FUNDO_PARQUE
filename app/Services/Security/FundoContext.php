<?php

namespace App\Services\Security;

class FundoContext
{
    protected static ?int $fundoId = null;

    public static function set(?int $fundoId): void
    {
        static::$fundoId = $fundoId;
    }

    public static function get(): ?int
    {
        if (static::$fundoId !== null) {
            return static::$fundoId;
        }

        if (function_exists('session') && session()->has('fundo_id')) {
            return (int) session('fundo_id');
        }

        return null;
    }

    public static function clear(): void
    {
        static::$fundoId = null;
    }
}
