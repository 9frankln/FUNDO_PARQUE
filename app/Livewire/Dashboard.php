<?php

namespace App\Livewire;

use App\Models\Animal;
use App\Models\Ordeno;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class Dashboard extends Component
{
    public array $dashboardData = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->dashboardData = [];

            return;
        }

        $fundoId = (int) session('fundo_id');
        if (! $fundoId) {
            $activeFundo = $user->fundos()->where('activo', true)->first();
            if ($activeFundo) {
                $fundoId = (int) $activeFundo->id;
                session(['fundo_id' => $fundoId]);
            } else {
                $this->dashboardData = [];

                return;
            }
        }

        $now = CarbonImmutable::now();
        $fundo = $user->fundoActivo();

        $allowedModules = collect(['animal', 'engorde', 'leche', 'queso', 'monitoreo', 'finanzas', 'medicamentos'])
            ->mapWithKeys(fn (string $module) => [$module => $user->tienePermiso($module, 'leer')])
            ->all();
        $createPermissions = collect(['animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo', 'medicamentos'])
            ->mapWithKeys(fn (string $module) => [$module => $user->tienePermiso($module, 'crear')])
            ->all();

        /*
         * OPTIMIZACIÓN DE RENDIMIENTO:
         * Las agregaciones del dashboard dependen solo del fundo + permisos del
         * módulo (no del usuario individual), así que se cachean 3 minutos por
         * clave `fundo + permisos`. La invalidez es automática por TTL; los datos
         * personales (saludo, nombre, fecha) se regeneran siempre en vivo.
         */
        $cacheKey = 'dashboard.stats.v3.'.$fundoId.'.'.md5(implode('', $allowedModules));

        $cached = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($fundoId, $allowedModules, $now): array {
            $today = $now->toDateString();
            $monthStart = $now->startOfMonth()->toDateString();
            $previousMonthStart = $now->subMonthNoOverflow()->startOfMonth()->toDateString();
            $previousMonthEnd = $now->subMonthNoOverflow()->endOfMonth()->toDateString();
            $thirtyDaysFromNow = $now->addDays(30)->toDateString();

            $animalStats = [
                'total' => 0,
                'newThisMonth' => 0,
                'inactive' => 0,
            ];
            $especiesData = [];
            if ($allowedModules['animal']) {
                $animalStats = [
                    'total' => Animal::where('fundo_id', $fundoId)->where('activo', true)->count(),
                    'newThisMonth' => Animal::where('fundo_id', $fundoId)
                        ->where('activo', true)
                        ->where('fecha_alta', '>=', $monthStart)
                        ->count(),
                    'inactive' => Animal::where('fundo_id', $fundoId)->where('activo', false)->count(),
                ];

                $especiesData = DB::table('animales')
                    ->leftJoin('especies', 'animales.especie_id', '=', 'especies.id')
                    ->where('animales.fundo_id', $fundoId)
                    ->where('animales.activo', true)
                    ->whereNull('animales.deleted_at')
                    ->selectRaw("COALESCE(especies.nombre, 'Sin especie') as label, COUNT(*) as count")
                    ->groupBy('especies.id', 'especies.nombre')
                    ->orderByDesc('count')
                    ->get()
                    ->map(fn ($item) => [
                        'label' => ucfirst((string) $item->label),
                        'count' => (int) $item->count,
                        'percentage' => $animalStats['total'] > 0
                            ? round(((int) $item->count / $animalStats['total']) * 100, 1)
                            : 0,
                    ])
                    ->all();
            }

            $milkStats = [
                'today' => 0,
                'month' => 0,
                'average7Days' => 0,
                'variation' => 0,
            ];
            if ($allowedModules['leche']) {
                $sevenDaysAgo = $now->subDays(6)->toDateString();
                $minDate = min($previousMonthStart, $sevenDaysAgo);

                $milkData = Ordeno::where('fundo_id', $fundoId)
                    ->where('fecha', '>=', $minDate)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN fecha = ? THEN litros_total ELSE 0 END), 0) as today,
                        COALESCE(SUM(CASE WHEN fecha >= ? THEN litros_total ELSE 0 END), 0) as month,
                        COALESCE(SUM(CASE WHEN fecha >= ? AND fecha <= ? THEN litros_total ELSE 0 END), 0) as prev_month,
                        COALESCE(SUM(CASE WHEN fecha >= ? THEN litros_total ELSE 0 END), 0) as last_7_days
                    ", [$today, $monthStart, $previousMonthStart, $previousMonthEnd, $sevenDaysAgo])
                    ->first();

                $milkStats = [
                    'today' => round((float) $milkData->today, 2),
                    'month' => round((float) $milkData->month, 2),
                    'average7Days' => round((float) $milkData->last_7_days / 7, 1),
                    'variation' => $this->variation((float) $milkData->month, (float) $milkData->prev_month),
                ];
            }

            $cheeseStats = [
                'todayKg' => 0,
                'monthKg' => 0,
                'monthUnits' => 0,
                'variation' => 0,
            ];
            if ($allowedModules['queso']) {
                $cheeseData = DB::table('producciones_queso')
                    ->where('fundo_id', $fundoId)
                    ->whereNull('deleted_at')
                    ->where('fecha', '>=', $previousMonthStart)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN fecha = ? THEN peso_total_kg ELSE 0 END), 0) as today_kg,
                        COALESCE(SUM(CASE WHEN fecha >= ? THEN peso_total_kg ELSE 0 END), 0) as month_kg,
                        COALESCE(SUM(CASE WHEN fecha >= ? THEN unidades ELSE 0 END), 0) as month_units,
                        COALESCE(SUM(CASE WHEN fecha >= ? AND fecha <= ? THEN peso_total_kg ELSE 0 END), 0) as prev_month_kg
                    ", [$today, $monthStart, $monthStart, $previousMonthStart, $previousMonthEnd])
                    ->first();

                $cheeseStats = [
                    'todayKg' => round((float) $cheeseData->today_kg, 2),
                    'monthKg' => round((float) $cheeseData->month_kg, 2),
                    'monthUnits' => (int) $cheeseData->month_units,
                    'variation' => $this->variation((float) $cheeseData->month_kg, (float) $cheeseData->prev_month_kg),
                ];
            }

            $financeStats = [
                'income' => 0,
                'expense' => 0,
                'balance' => 0,
                'variation' => 0,
            ];
            if ($allowedModules['finanzas']) {
                $financeData = DB::table('movimientos')
                    ->where('fundo_id', $fundoId)
                    ->whereNull('deleted_at')
                    ->where('fecha', '>=', $previousMonthStart)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN fecha >= ? AND tipo = 'ingreso' THEN monto ELSE 0 END), 0) as income,
                        COALESCE(SUM(CASE WHEN fecha >= ? AND tipo = 'egreso' THEN monto ELSE 0 END), 0) as expense,
                        COALESCE(SUM(CASE WHEN fecha >= ? AND fecha <= ? AND tipo = 'ingreso' THEN monto WHEN fecha >= ? AND fecha <= ? AND tipo = 'egreso' THEN -monto ELSE 0 END), 0) as prev_balance
                    ", [$monthStart, $monthStart, $previousMonthStart, $previousMonthEnd, $previousMonthStart, $previousMonthEnd])
                    ->first();

                $currentBalance = (float) $financeData->income - (float) $financeData->expense;

                $financeStats = [
                    'income' => round((float) $financeData->income, 2),
                    'expense' => round((float) $financeData->expense, 2),
                    'balance' => round($currentBalance, 2),
                    'variation' => $this->variation($currentBalance, (float) $financeData->prev_balance),
                ];
            }

            $fatteningStats = [
                'active' => 0,
                'ready' => 0,
                'lots' => 0,
            ];
            if ($allowedModules['engorde']) {
                $fatteningStats = [
                    'active' => DB::table('engorde_animales')
                        ->join('lotes_engorde', 'engorde_animales.lote_id', '=', 'lotes_engorde.id')
                        ->where('lotes_engorde.fundo_id', $fundoId)
                        ->whereNull('engorde_animales.deleted_at')
                        ->where('engorde_animales.estado', 'engorde_activo')
                        ->count(),
                    'ready' => DB::table('engorde_animales')
                        ->join('lotes_engorde', 'engorde_animales.lote_id', '=', 'lotes_engorde.id')
                        ->where('lotes_engorde.fundo_id', $fundoId)
                        ->whereNull('engorde_animales.deleted_at')
                        ->where('engorde_animales.estado', 'listo_venta')
                        ->count(),
                    'lots' => DB::table('lotes_engorde')
                        ->where('fundo_id', $fundoId)
                        ->whereNull('deleted_at')
                        ->where('estado', 'activo')
                        ->count(),
                ];
            }

            $botiquinStats = [
                'totalMedicamentos' => 0,
                'totalInsumos' => 0,
                'lowStockCount' => 0,
                'expiringSoonCount' => 0,
            ];
            if ($allowedModules['medicamentos']) {
                $stockMedSql = '(SELECT COALESCE(SUM(ml.cantidad_disponible), 0) FROM medicamento_lotes ml WHERE ml.medicamento_id = medicamentos.id AND ml.fundo_id = ? AND ml.activo = 1)';
                $stockInsSql = '(SELECT COALESCE(SUM(il.cantidad_disponible), 0) FROM insumo_lotes il WHERE il.insumo_id = insumos.id AND il.fundo_id = ? AND il.activo = 1)';

                $botiquinStats = [
                    'totalMedicamentos' => DB::table('medicamentos')
                        ->where(function ($q) use ($fundoId) {
                            $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id');
                        })
                        ->where('activo', true)
                        ->count(),
                    'totalInsumos' => DB::table('insumos')
                        ->where(function ($q) use ($fundoId) {
                            $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id');
                        })
                        ->where('activo', true)
                        ->count(),
                    'lowStockCount' => DB::table('medicamentos')
                        ->where(function ($q) use ($fundoId) {
                            $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id');
                        })
                        ->where('activo', true)
                        ->whereRaw($stockMedSql.' <= medicamentos.stock_minimo', [$fundoId])
                        ->count() +
                        DB::table('insumos')
                        ->where(function ($q) use ($fundoId) {
                            $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id');
                        })
                        ->where('activo', true)
                        ->whereRaw($stockInsSql.' <= insumos.stock_minimo', [$fundoId])
                        ->count(),
                    'expiringSoonCount' => DB::table('medicamento_lotes')
                        ->where('fundo_id', $fundoId)
                        ->where('cantidad_disponible', '>', 0)
                        ->where('activo', true)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '<=', $thirtyDaysFromNow)
                        ->count() +
                        DB::table('insumo_lotes')
                        ->where('fundo_id', $fundoId)
                        ->where('cantidad_disponible', '>', 0)
                        ->where('activo', true)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '<=', $thirtyDaysFromNow)
                        ->count(),
                ];
            }

            $alertStats = [
                'pending' => 0,
                'overdue' => 0,
                'today' => 0,
                'partosMonth' => 0,
                'sanidadMonth' => 0,
            ];
            $priorities = [];
            if ($allowedModules['monitoreo']) {
                $alertStats = [
                    'pending' => DB::table('alertas_programadas')
                        ->where('fundo_id', $fundoId)
                        ->where('leida', false)
                        ->count(),
                    'overdue' => DB::table('alertas_programadas')
                        ->where('fundo_id', $fundoId)
                        ->where('leida', false)
                        ->where('fecha_alerta', '<', $today)
                        ->count(),
                    'today' => DB::table('alertas_programadas')
                        ->where('fundo_id', $fundoId)
                        ->where('leida', false)
                        ->where('fecha_alerta', $today)
                        ->count(),
                    'partosMonth' => DB::table('partos')
                        ->where('fundo_id', $fundoId)
                        ->whereNull('deleted_at')
                        ->where('fecha_parto', '>=', $monthStart)
                        ->count(),
                    'sanidadMonth' => DB::table('sanidad_registros')
                        ->where('fundo_id', $fundoId)
                        ->whereNull('deleted_at')
                        ->where('fecha_evento', '>=', $monthStart)
                        ->count(),
                ];

                $priorities = DB::table('alertas_programadas')
                    ->leftJoin('animales', 'alertas_programadas.animal_id', '=', 'animales.id')
                    ->where('alertas_programadas.fundo_id', $fundoId)
                    ->where('alertas_programadas.leida', false)
                    ->select([
                        'alertas_programadas.id',
                        'alertas_programadas.tipo',
                        'alertas_programadas.fecha_alerta',
                        'alertas_programadas.mensaje',
                        'animales.arete',
                        'animales.nombre as animal_nombre',
                    ])
                    ->orderBy('alertas_programadas.fecha_alerta')
                    ->limit(5)
                    ->get()
                    ->map(function ($alert) use ($today) {
                        $date = CarbonImmutable::parse($alert->fecha_alerta);

                        return [
                            'id' => (int) $alert->id,
                            'type' => Str::headline((string) $alert->tipo),
                            'message' => $alert->mensaje ?: 'Revisión programada',
                            'animal' => $alert->arete
                                ? trim($alert->arete.' · '.($alert->animal_nombre ?: 'Sin nombre'))
                                : null,
                            'date' => $date->locale('es')->translatedFormat('d M'),
                            'status' => $date->toDateString() < $today
                                ? 'overdue'
                                : ($date->toDateString() === $today ? 'today' : 'upcoming'),
                        ];
                    })
                    ->all();
            }

            $monthDates = collect(range(11, 0))
                ->map(fn (int $offset) => $now->subMonthsNoOverflow($offset)->startOfMonth());
            $monthPeriods = $monthDates->map(fn (CarbonImmutable $date) => $date->format('Y-m'));
            $minimumDate = $monthDates->first()->toDateString();

            $milkRaw = $allowedModules['leche']
                ? DB::table('ordenos')
                    ->where('fundo_id', $fundoId)
                    ->whereNull('deleted_at')
                    ->where('fecha', '>=', $minimumDate)
                    ->selectRaw('substr(fecha, 1, 7) as period, SUM(litros_total) as total, COUNT(*) as records')
                    ->groupBy('period')
                    ->get()
                    ->keyBy('period')
                : collect();
            $cheeseRaw = $allowedModules['queso']
                ? DB::table('producciones_queso')
                    ->where('fundo_id', $fundoId)
                    ->whereNull('deleted_at')
                    ->where('fecha', '>=', $minimumDate)
                    ->selectRaw('substr(fecha, 1, 7) as period, SUM(peso_total_kg) as total, SUM(unidades) as units')
                    ->groupBy('period')
                    ->get()
                    ->keyBy('period')
                : collect();
            $financeRaw = $allowedModules['finanzas']
                ? DB::table('movimientos')
                    ->where('fundo_id', $fundoId)
                    ->whereNull('deleted_at')
                    ->where('fecha', '>=', $minimumDate)
                    ->selectRaw('substr(fecha, 1, 7) as period, tipo, SUM(monto) as total')
                    ->groupBy('period', 'tipo')
                    ->get()
                : collect();

            $monthLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $series = function (CarbonImmutable $date, $raw, string $secondary = 'records') use ($monthLabels): array {
                $period = $date->format('Y-m');
                $record = $raw->get($period);

                return [
                    'period' => $period,
                    'label' => $monthLabels[$date->month - 1].' '.$date->format('y'),
                    'total' => round((float) ($record->total ?? 0), 2),
                    $secondary => (int) ($record->{$secondary} ?? 0),
                ];
            };

            $milkMonthly = $monthDates->map(fn (CarbonImmutable $date) => $series($date, $milkRaw))->all();
            $cheeseMonthly = $monthDates->map(fn (CarbonImmutable $date) => $series($date, $cheeseRaw, 'units'))->all();
            $incomeByMonth = $financeRaw->where('tipo', 'ingreso')->keyBy('period');
            $expenseByMonth = $financeRaw->where('tipo', 'egreso')->keyBy('period');
            $financeMonthly = $monthDates->map(function (CarbonImmutable $date) use ($monthLabels, $incomeByMonth, $expenseByMonth): array {
                $period = $date->format('Y-m');
                $income = round((float) ($incomeByMonth->get($period)->total ?? 0), 2);
                $expense = round((float) ($expenseByMonth->get($period)->total ?? 0), 2);

                return [
                    'period' => $period,
                    'label' => $monthLabels[$date->month - 1].' '.$date->format('y'),
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => round($income - $expense, 2),
                ];
            })->all();

            return [
                'animalStats' => $animalStats,
                'milkStats' => $milkStats,
                'cheeseStats' => $cheeseStats,
                'financeStats' => $financeStats,
                'fatteningStats' => $fatteningStats,
                'botiquinStats' => $botiquinStats,
                'alertStats' => $alertStats,
                'priorities' => $priorities,
                'especiesData' => $especiesData,
                'milkMonthly' => $milkMonthly,
                'cheeseMonthly' => $cheeseMonthly,
                'financeMonthly' => $financeMonthly,
                'monthPeriods' => $monthPeriods->all(),
            ];
        });

        // Datos personales (siempre en vivo, no cacheables)
        $displayName = Str::title(Str::lower(trim((string) $user->name)));
        $firstName = Str::before($displayName, ' ');
        $greeting = match (true) {
            $now->hour < 12 => 'Buenos días',
            $now->hour < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        $this->dashboardData = [
            'generatedAt' => $now->format('H:i'),
            'refreshKey' => $now->format('YmdHisv'),
            'welcome' => [
                'greeting' => $greeting,
                'name' => $firstName ?: $displayName,
                'fundo' => $fundo?->nombre ?: 'Fundo seleccionado',
                'date' => Str::ucfirst($now->locale('es')->translatedFormat('l, d \d\e F')),
                'message' => $cached['alertStats']['overdue'] > 0
                    ? 'Hay '.$cached['alertStats']['overdue'].' alertas vencidas que conviene atender primero.'
                    : 'Aquí tienes el estado esencial del fundo para organizar la jornada.',
            ],
            'allowedModules' => $allowedModules,
            'createPermissions' => $createPermissions,
            'kpis' => [
                'animals' => $cached['animalStats'],
                'milk' => $cached['milkStats'],
                'cheese' => $cached['cheeseStats'],
                'finance' => $cached['financeStats'],
                'fattening' => $cached['fatteningStats'],
                'botiquin' => $cached['botiquinStats'],
                'alerts' => $cached['alertStats'],
            ],
            'priorities' => $cached['priorities'],
            'species' => $cached['especiesData'],
            'milkMonthly' => $cached['milkMonthly'],
            'cheeseMonthly' => $cached['cheeseMonthly'],
            'financeMonthly' => $cached['financeMonthly'],
            'periodOptions' => $cached['monthPeriods'],
        ];
    }

    private function variation(float $current, float $previous): float
    {
        if (abs($previous) < 0.00001) {
            return $current === 0.0 ? 0 : 100;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app');
    }
}
