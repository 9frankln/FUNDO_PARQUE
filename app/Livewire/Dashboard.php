<?php

namespace App\Livewire;

use App\Models\Animal;
use App\Models\Ordeno;
use Carbon\CarbonImmutable;
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

    public function hydrate(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $fundoId = (int) session('fundo_id');
        $user = auth()->user();

        if (! $fundoId || ! $user) {
            $this->dashboardData = [];

            return;
        }

        $now = CarbonImmutable::now();
        $today = $now->toDateString();
        $monthStart = $now->startOfMonth()->toDateString();
        $previousMonthStart = $now->subMonthNoOverflow()->startOfMonth()->toDateString();
        $previousMonthEnd = $now->subMonthNoOverflow()->endOfMonth()->toDateString();
        $fundo = $user->fundoActivo();

        $allowedModules = collect(['animal', 'engorde', 'leche', 'queso', 'monitoreo', 'finanzas'])
            ->mapWithKeys(fn (string $module) => [$module => $user->tienePermiso($module, 'leer')])
            ->all();
        $createPermissions = collect(['animal', 'leche', 'queso', 'finanzas', 'monitoreo'])
            ->mapWithKeys(fn (string $module) => [$module => $user->tienePermiso($module, 'crear')])
            ->all();

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
                    ->whereDate('fecha_alta', '>=', $monthStart)
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
            $milkToday = (float) Ordeno::where('fundo_id', $fundoId)->whereDate('fecha', $today)->sum('litros_total');
            $milkMonth = (float) Ordeno::where('fundo_id', $fundoId)->whereDate('fecha', '>=', $monthStart)->sum('litros_total');
            $milkPreviousMonth = (float) Ordeno::where('fundo_id', $fundoId)
                ->whereBetween('fecha', [$previousMonthStart, $previousMonthEnd])
                ->sum('litros_total');
            $milkLastSevenDays = (float) Ordeno::where('fundo_id', $fundoId)
                ->whereDate('fecha', '>=', $now->subDays(6)->toDateString())
                ->sum('litros_total');

            $milkStats = [
                'today' => round($milkToday, 2),
                'month' => round($milkMonth, 2),
                'average7Days' => round($milkLastSevenDays / 7, 1),
                'variation' => $this->variation($milkMonth, $milkPreviousMonth),
            ];
        }

        $cheeseStats = [
            'todayKg' => 0,
            'monthKg' => 0,
            'monthUnits' => 0,
            'variation' => 0,
        ];
        if ($allowedModules['queso']) {
            $cheeseTodayKg = (float) DB::table('producciones_queso')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->whereDate('fecha', $today)
                ->sum('peso_total_kg');
            $cheeseMonth = DB::table('producciones_queso')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->whereDate('fecha', '>=', $monthStart)
                ->selectRaw('COALESCE(SUM(peso_total_kg), 0) as kg, COALESCE(SUM(unidades), 0) as units')
                ->first();
            $cheesePreviousMonthKg = (float) DB::table('producciones_queso')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->whereBetween('fecha', [$previousMonthStart, $previousMonthEnd])
                ->sum('peso_total_kg');

            $cheeseStats = [
                'todayKg' => round($cheeseTodayKg, 2),
                'monthKg' => round((float) $cheeseMonth->kg, 2),
                'monthUnits' => (int) $cheeseMonth->units,
                'variation' => $this->variation((float) $cheeseMonth->kg, $cheesePreviousMonthKg),
            ];
        }

        $financeStats = [
            'income' => 0,
            'expense' => 0,
            'balance' => 0,
            'variation' => 0,
        ];
        if ($allowedModules['finanzas']) {
            $financeMonth = DB::table('movimientos')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->whereDate('fecha', '>=', $monthStart)
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) as income")
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END), 0) as expense")
                ->first();
            $previousBalance = (float) (DB::table('movimientos')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->whereBetween('fecha', [$previousMonthStart, $previousMonthEnd])
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END), 0) as balance")
                ->first()->balance ?? 0);
            $currentBalance = (float) $financeMonth->income - (float) $financeMonth->expense;

            $financeStats = [
                'income' => round((float) $financeMonth->income, 2),
                'expense' => round((float) $financeMonth->expense, 2),
                'balance' => round($currentBalance, 2),
                'variation' => $this->variation($currentBalance, $previousBalance),
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

        $alertStats = [
            'pending' => 0,
            'overdue' => 0,
            'today' => 0,
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
                    ->whereDate('fecha_alerta', '<', $today)
                    ->count(),
                'today' => DB::table('alertas_programadas')
                    ->where('fundo_id', $fundoId)
                    ->where('leida', false)
                    ->whereDate('fecha_alerta', $today)
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
                ->whereDate('fecha', '>=', $minimumDate)
                ->selectRaw('substr(fecha, 1, 7) as period, SUM(litros_total) as total, COUNT(*) as records')
                ->groupBy('period')
                ->get()
                ->keyBy('period')
            : collect();
        $cheeseRaw = $allowedModules['queso']
            ? DB::table('producciones_queso')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->whereDate('fecha', '>=', $minimumDate)
                ->selectRaw('substr(fecha, 1, 7) as period, SUM(peso_total_kg) as total, SUM(unidades) as units')
                ->groupBy('period')
                ->get()
                ->keyBy('period')
            : collect();
        $financeRaw = $allowedModules['finanzas']
            ? DB::table('movimientos')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->whereDate('fecha', '>=', $minimumDate)
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
                'message' => $alertStats['overdue'] > 0
                    ? 'Hay '.$alertStats['overdue'].' alertas vencidas que conviene atender primero.'
                    : 'Aquí tienes el estado esencial del fundo para organizar la jornada.',
            ],
            'allowedModules' => $allowedModules,
            'createPermissions' => $createPermissions,
            'kpis' => [
                'animals' => $animalStats,
                'milk' => $milkStats,
                'cheese' => $cheeseStats,
                'finance' => $financeStats,
                'fattening' => $fatteningStats,
                'alerts' => $alertStats,
            ],
            'priorities' => $priorities,
            'species' => $especiesData,
            'milkMonthly' => $milkMonthly,
            'cheeseMonthly' => $cheeseMonthly,
            'financeMonthly' => $financeMonthly,
            'periodOptions' => $monthPeriods->all(),
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
