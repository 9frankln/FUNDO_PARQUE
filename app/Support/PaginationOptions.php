<?php

namespace App\Support;

/**
 * Opciones compartidas de paginación, meses y periodos.
 *
 * Antes estos arrays se duplicaban en decenas de componentes y vistas
 * (Animal, Leche, Engorde, Queso, Finanzas, Insumos, Medicamentos, Ajustes...).
 * Fuente única: si cambia una opción se edita solo aquí.
 */
class PaginationOptions
{
    /** Valores disponibles para "registros por página". */
    public const PER_PAGE = [10, 25, 50, 100];

    /** Valores usados en algunos módulos con tablas densas. */
    public const PER_PAGE_COMPACT = [5, 10, 20, 50];

    /**
     * Opciones "registros por página" formateadas para x-filter-select.
     *
     * @return array<string, string>
     */
    public static function perPageOptions(): array
    {
        return array_combine(
            array_map('strval', self::PER_PAGE),
            array_map(fn (int $value) => "{$value} registros", self::PER_PAGE)
        );
    }

    /**
     * Variante compacta (5, 10, 20, 50) para tablas de detalle densas.
     *
     * @return array<string, string>
     */
    public static function perPageCompactOptions(): array
    {
        return array_combine(
            array_map('strval', self::PER_PAGE_COMPACT),
            array_map(fn (int $value) => "{$value} registros", self::PER_PAGE_COMPACT)
        );
    }

    /** Valida/ajusta un valor a la lista permitida. */
    public static function normalize(int $value, ?int $default = null): int
    {
        return in_array($value, self::PER_PAGE, true) ? $value : ($default ?? 10);
    }

    /**
     * Meses del año formateados (1 => Enero ... 12 => Diciembre).
     *
     * @return array<string, string>
     */
    public static function months(): array
    {
        return [
            '1' => 'Enero',
            '2' => 'Febrero',
            '3' => 'Marzo',
            '4' => 'Abril',
            '5' => 'Mayo',
            '6' => 'Junio',
            '7' => 'Julio',
            '8' => 'Agosto',
            '9' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];
    }
}
