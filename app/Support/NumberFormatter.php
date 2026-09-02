<?php

namespace App\Support;

/**
 * Formateo numérico compartido (antes duplicado en Medicamentos/Form,
 * Insumos/Form y Finanzas/MovimientoForm).
 */
class NumberFormatter
{
    /**
     * Devuelve el número sin decimales innecesarios (100 en lugar de 100.00),
     * o una cadena vacía para valores nulos.
     */
    public static function format(mixed $value, int $precision = 2): int|float|string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $num = (float) round((float) $value, $precision);

        return floor($num) == $num ? (int) $num : $num;
    }
}
