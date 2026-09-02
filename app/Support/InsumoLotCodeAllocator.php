<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InsumoLotCodeAllocator extends SequentialCodeAllocator
{
    public const PREFIX = 'INS';

    protected function lotsTable(): string
    {
        return 'insumo_lotes';
    }

    protected function sequencesTable(): string
    {
        return 'insumo_lot_code_sequences';
    }

    protected function yearColumn(): string
    {
        return 'anio';
    }

    protected function itemLabel(): string
    {
        return 'insumo';
    }
}
