<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicamentoLotCodeAllocator extends SequentialCodeAllocator
{
    public const PREFIX = 'MET';

    protected function lotsTable(): string
    {
        return 'medicamento_lotes';
    }

    protected function sequencesTable(): string
    {
        return 'medicamento_lot_code_sequences';
    }

    protected function yearColumn(): string
    {
        return 'codigo_anio';
    }

    protected function itemLabel(): string
    {
        return 'medicamento';
    }
}
