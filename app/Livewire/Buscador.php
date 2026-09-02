<?php

namespace App\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class Buscador extends Component
{
    private const CATEGORIES = [
        'animal' => ['label' => 'Animales', 'module' => 'animal'],
        'engorde' => ['label' => 'Engorde', 'module' => 'engorde'],
        'leche' => ['label' => 'Leche', 'module' => 'leche'],
        'queso' => ['label' => 'Queso', 'module' => 'queso'],
        'monitoreo' => ['label' => 'Sanidad', 'module' => 'monitoreo'],
        'finanzas' => ['label' => 'Finanzas', 'module' => 'finanzas'],
        'auditoria' => ['label' => 'Auditoría', 'module' => 'auditoria'],
    ];

    public string $search = '';

    public string $categoria = 'todos';

    public array $resultados = [];

    public array $resultCounts = [];

    public array $availableCategories = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'categoria' => ['except' => 'todos'],
    ];

    public function mount(): void
    {
        $this->loadAvailableCategories();

        if (! array_key_exists($this->categoria, $this->availableCategories)) {
            $this->categoria = 'todos';
        }

        $this->performSearch();
    }

    public function setCategoria(string $category): void
    {
        if (! array_key_exists($category, $this->availableCategories)) {
            return;
        }

        $this->categoria = $category;
        $this->performSearch();
    }

    public function updatedSearch(): void
    {
        $this->performSearch();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resultados = [];
        $this->resultCounts = [];
    }

    private function loadAvailableCategories(): void
    {
        $user = auth()->user();
        $categories = [
            'todos' => [
                'label' => 'Todos',
                'description' => 'Todos los módulos autorizados',
            ],
        ];

        foreach (self::CATEGORIES as $key => $category) {
            if ($user?->tienePermiso($category['module'], 'leer')) {
                $categories[$key] = [
                    'label' => $category['label'],
                    'description' => $this->categoryDescription($key),
                ];
            }
        }

        $this->availableCategories = $categories;
    }

    private function performSearch(): void
    {
        $search = trim($this->search);
        if (mb_strlen($search) < 2) {
            $this->resultados = [];
            $this->resultCounts = [];

            return;
        }

        $fundoId = (int) session('fundo_id');
        if (! $fundoId || ! auth()->user()) {
            $this->resultados = [];
            $this->resultCounts = [];

            return;
        }

        $term = '%'.$search.'%';
        $normalizedTerm = '%'.str_replace([' ', '-'], '_', Str::lower($search)).'%';
        $dateTerm = $this->normalizedDateTerm($search);
        $numericTerm = $this->numericTerm($search);
        $cleanSearch = trim($search);

        /*
         * OPTIMIZACIÓN DE RENDIMIENTO:
         * Los resultados se cachean 60 segundos por fundo + categoría + término.
         * Evita repetir hasta 7 consultas LIKE cuando se navega entre categorías
         * con el mismo término o se reescribe una búsqueda reciente.
         */
        $cacheKey = 'buscador.v1.'.$fundoId.'.'.$this->categoria.'.'.md5(implode('|', [
            strtolower($search),
            implode(',', array_keys($this->availableCategories)),
        ]));

        $cached = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($fundoId, $term, $normalizedTerm, $dateTerm, $numericTerm, $cleanSearch): array {
            $results = collect();

            if ($this->shouldSearch('animal')) {
                $results = $results->merge($this->searchAnimals($fundoId, $term, $normalizedTerm, $dateTerm, $cleanSearch));
            }
            if ($this->shouldSearch('engorde')) {
                $results = $results->merge($this->searchFatteningLots($fundoId, $term, $normalizedTerm, $dateTerm));
            }
            if ($this->shouldSearch('leche')) {
                $results = $results->merge($this->searchMilkRecords($fundoId, $term, $normalizedTerm, $dateTerm, $numericTerm));
            }
            if ($this->shouldSearch('queso')) {
                $results = $results->merge($this->searchCheeseProduction($fundoId, $term, $dateTerm, $numericTerm));
            }
            if ($this->shouldSearch('finanzas')) {
                $results = $results->merge($this->searchFinancialMovements($fundoId, $term, $normalizedTerm, $dateTerm, $numericTerm, $cleanSearch));
            }
            if ($this->shouldSearch('monitoreo')) {
                $results = $results->merge($this->searchHealthRecords($fundoId, $term, $normalizedTerm, $dateTerm));
            }
            if ($this->shouldSearch('auditoria')) {
                $results = $results->merge($this->searchAuditLog($fundoId, $term, $normalizedTerm));
            }

            $sorted = $results
                ->sortByDesc('created_at')
                ->take(60)
                ->values();

            return [
                'resultados' => $sorted->all(),
                'resultCounts' => $sorted
                    ->countBy('type')
                    ->map(fn (int $count) => $count)
                    ->all(),
            ];
        });

        $this->resultados = $cached['resultados'];
        $this->resultCounts = $cached['resultCounts'];
    }

    private function searchAnimals(int $fundoId, string $term, string $normalizedTerm, ?string $dateTerm, string $cleanSearch): Collection
    {
        return DB::table('animales')
            ->leftJoin('especies', 'animales.especie_id', '=', 'especies.id')
            ->leftJoin('razas', 'animales.raza_id', '=', 'razas.id')
            ->where('animales.fundo_id', $fundoId)
            ->whereNull('animales.deleted_at')
            ->where(function ($query) use ($term, $normalizedTerm, $dateTerm, $cleanSearch) {
                $query->where(function ($q) use ($term) {
                    $q->where('animales.arete', 'like', $term)->orWhere('animales.nombre', 'like', $term);
                })
                ->orWhere('especies.nombre', 'like', $term)
                ->orWhere('razas.nombre', 'like', $term)
                ->orWhere('animales.genero', 'like', $normalizedTerm)
                ->orWhere('animales.estado_productivo', 'like', $normalizedTerm)
                ->orWhere('animales.estado_reproductivo', 'like', $normalizedTerm)
                ->orWhere('animales.tipo_alta', 'like', $normalizedTerm)
                ->orWhere('animales.observaciones', 'like', $term);
                if ($dateTerm) {
                    $query->orWhere('animales.fecha_alta', $dateTerm);
                }
            })
            ->select([
                'animales.id',
                'animales.arete',
                'animales.nombre',
                'animales.genero',
                'animales.estado_productivo',
                'animales.activo',
                'animales.created_at',
                'especies.nombre as especie_nombre',
                'razas.nombre as raza_nombre',
            ])
            ->limit(20)
            ->get()
            ->map(fn ($row) => $this->result(
                type: 'animal',
                label: 'Animal',
                title: $row->arete.($row->nombre ? ' · '.$row->nombre : ''),
                description: ($row->especie_nombre ?: 'Sin especie').' · '.($row->raza_nombre ?: 'Sin raza').' · '.Str::headline((string) $row->estado_productivo),
                meta: $row->activo ? 'En inventario' : 'Dado de baja',
                url: route('animal.show', $row->id),
                createdAt: $row->created_at,
            ));
    }

    private function searchFatteningLots(int $fundoId, string $term, string $normalizedTerm, ?string $dateTerm): Collection
    {
        return DB::table('lotes_engorde')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($term, $normalizedTerm, $dateTerm) {
                $query->where('codigo', 'like', $term)
                    ->orWhere('nombre', 'like', $term)
                    ->orWhere('estado', 'like', $normalizedTerm)
                    ->orWhere('observaciones', 'like', $term)
                    ->orWhere('fecha_inicio', 'like', $term);
                if ($dateTerm) {
                    $query->orWhere('fecha_inicio', 'like', $dateTerm);
                }
            })
            ->select(['id', 'codigo', 'nombre', 'estado', 'fecha_inicio', 'created_at'])
            ->limit(15)
            ->get()
            ->map(fn ($row) => $this->result(
                type: 'engorde',
                label: 'Lote de engorde',
                title: $row->codigo.($row->nombre ? ' · '.$row->nombre : ''),
                description: 'Inicio '.$this->dateLabel($row->fecha_inicio).' · '.Str::headline((string) $row->estado),
                meta: 'Lote',
                url: route('engorde.lote.show', $row->id),
                createdAt: $row->created_at,
            ));
    }

    private function searchMilkRecords(int $fundoId, string $term, string $normalizedTerm, ?string $dateTerm, ?float $numericTerm): Collection
    {
        return DB::table('ordenos')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($term, $normalizedTerm, $dateTerm, $numericTerm) {
                $query->where('turno', 'like', $normalizedTerm)
                    ->orWhere('tipo_registro', 'like', $normalizedTerm)
                    ->orWhere('observaciones', 'like', $term);
                if ($numericTerm !== null) {
                    $query->orWhere('litros_total', $numericTerm);
                }
                if ($dateTerm) {
                    $query->orWhere('fecha', $dateTerm);
                }
            })
            ->select(['id', 'fecha', 'turno', 'tipo_registro', 'litros_total', 'cantidad_vacas', 'observaciones', 'created_at'])
            ->limit(15)
            ->get()
            ->map(fn ($row) => $this->result(
                type: 'leche',
                label: 'Ordeño',
                title: $this->dateLabel($row->fecha).' · '.number_format((float) $row->litros_total, 1).' L',
                description: Str::headline((string) $row->turno).' · '.$row->cantidad_vacas.' animales · '.Str::headline((string) $row->tipo_registro),
                meta: $row->observaciones ?: 'Registro de producción',
                url: route('leche.show', $row->id),
                createdAt: $row->created_at,
            ));
    }

    private function searchCheeseProduction(int $fundoId, string $term, ?string $dateTerm, ?float $numericTerm): Collection
    {
        return DB::table('producciones_queso')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($term, $dateTerm, $numericTerm) {
                $query->where('observaciones', 'like', $term);
                if ($numericTerm !== null) {
                    $query->orWhere('unidades', $numericTerm)
                          ->orWhere('peso_total_kg', $numericTerm);
                }
                if ($dateTerm) {
                    $query->orWhere('fecha', $dateTerm);
                }
            })
            ->select(['id', 'fecha', 'unidades', 'peso_total_kg', 'observaciones', 'created_at'])
            ->limit(15)
            ->get()
            ->map(fn ($row) => $this->result(
                type: 'queso',
                label: 'Producción de queso',
                title: $this->dateLabel($row->fecha).' · '.number_format((float) $row->peso_total_kg, 1).' kg',
                description: $row->unidades.' unidades producidas',
                meta: $row->observaciones ?: 'Elaboración registrada',
                url: route('queso.show', $row->id),
                createdAt: $row->created_at,
            ));
    }

    private function searchFinancialMovements(int $fundoId, string $term, string $normalizedTerm, ?string $dateTerm, ?float $numericTerm, string $cleanSearch): Collection
    {
        return DB::table('movimientos')
            ->leftJoin('categorias_financieras', 'movimientos.categoria_id', '=', 'categorias_financieras.id')
            ->where('movimientos.fundo_id', $fundoId)
            ->whereNull('movimientos.deleted_at')
            ->where(function ($query) use ($term, $normalizedTerm, $dateTerm, $numericTerm, $cleanSearch) {
                $query->where('movimientos.descripcion', 'like', $term)
                ->orWhere('movimientos.tipo', 'like', $normalizedTerm)
                ->orWhere('categorias_financieras.nombre', 'like', $term);
                if ($numericTerm !== null) {
                    $query->orWhere('movimientos.monto', $numericTerm);
                }
                if ($dateTerm) {
                    $query->orWhere('movimientos.fecha', $dateTerm);
                }
            })
            ->select([
                'movimientos.id',
                'movimientos.tipo',
                'movimientos.monto',
                'movimientos.moneda',
                'movimientos.fecha',
                'movimientos.descripcion',
                'movimientos.created_at',
                'categorias_financieras.nombre as categoria_nombre',
            ])
            ->limit(20)
            ->get()
            ->map(fn ($row) => $this->result(
                type: 'finanzas',
                label: 'Movimiento financiero',
                title: Str::headline((string) $row->tipo).' · S/ '.number_format((float) $row->monto, 2),
                description: ($row->categoria_nombre ?: 'Sin categoría').' · '.($row->descripcion ?: 'Sin descripción'),
                meta: $this->dateLabel($row->fecha),
                url: route('finanzas.movimiento.show', $row->id),
                createdAt: $row->created_at,
            ));
    }

    private function searchHealthRecords(int $fundoId, string $term, string $normalizedTerm, ?string $dateTerm): Collection
    {
        $canUpdate = auth()->user()?->tienePermiso('monitoreo', 'actualizar') ?? false;

        return DB::table('sanidad_registros')
            ->leftJoin('animales', 'sanidad_registros.animal_id', '=', 'animales.id')
            ->leftJoin('medicamentos', 'sanidad_registros.medicamento_id', '=', 'medicamentos.id')
            ->where('sanidad_registros.fundo_id', $fundoId)
            ->whereNull('sanidad_registros.deleted_at')
            ->where(function ($query) use ($term, $normalizedTerm, $dateTerm) {
                $query->where('sanidad_registros.sintomas_diagnostico', 'like', $term)
                    ->orWhere('sanidad_registros.tratamiento', 'like', $term)
                    ->orWhere('sanidad_registros.clasificacion', 'like', $normalizedTerm)
                    ->orWhere('sanidad_registros.estado_clinico', 'like', $normalizedTerm)
                    ->orWhere('sanidad_registros.fecha_evento', 'like', $term)
                    ->orWhere('animales.arete', 'like', $term)
                    ->orWhere('animales.nombre', 'like', $term)
                    ->orWhere('medicamentos.nombre', 'like', $term);
                if ($dateTerm) {
                    $query->orWhere('sanidad_registros.fecha_evento', 'like', $dateTerm);
                }
            })
            ->select([
                'sanidad_registros.id',
                'sanidad_registros.fecha_evento',
                'sanidad_registros.clasificacion',
                'sanidad_registros.estado_clinico',
                'sanidad_registros.sintomas_diagnostico',
                'sanidad_registros.tratamiento',
                'sanidad_registros.created_at',
                'animales.arete',
                'animales.nombre as animal_nombre',
                'medicamentos.nombre as medicamento_nombre',
            ])
            ->limit(15)
            ->get()
            ->map(fn ($row) => $this->result(
                type: 'monitoreo',
                label: 'Registro sanitario',
                title: ($row->arete ?: 'Animal').' · '.Str::headline((string) $row->clasificacion),
                description: $row->sintomas_diagnostico ?: ($row->tratamiento ?: 'Seguimiento clínico'),
                meta: $this->dateLabel($row->fecha_evento).' · '.Str::headline((string) $row->estado_clinico),
                url: $canUpdate ? route('monitoreo.sanidad.edit', $row->id) : route('monitoreo.index'),
                createdAt: $row->created_at,
            ));
    }

    private function searchAuditLog(int $fundoId, string $term, string $normalizedTerm): Collection
    {
        return DB::table('auditoria_logs')
            ->leftJoin('users', 'auditoria_logs.user_id', '=', 'users.id')
            ->where('auditoria_logs.fundo_id', $fundoId)
            ->where(function ($query) use ($term, $normalizedTerm) {
                $query->where('auditoria_logs.accion', 'like', $normalizedTerm)
                    ->orWhere('auditoria_logs.modulo', 'like', $normalizedTerm)
                    ->orWhere('auditoria_logs.detalle', 'like', $term)
                    ->orWhere('users.name', 'like', $term);
            })
            ->select([
                'auditoria_logs.id',
                'auditoria_logs.accion',
                'auditoria_logs.modulo',
                'auditoria_logs.detalle',
                'auditoria_logs.created_at',
                'users.name as user_name',
            ])
            ->limit(15)
            ->get()
            ->map(fn ($row) => $this->result(
                type: 'auditoria',
                label: 'Auditoría',
                title: Str::headline((string) $row->accion).' · '.Str::headline((string) $row->modulo),
                description: $row->detalle ?: 'Actividad registrada en el sistema',
                meta: $row->user_name ?: 'Sistema',
                url: route('auditoria.index'),
                createdAt: $row->created_at,
            ));
    }

    private function result(
        string $type,
        string $label,
        string $title,
        string $description,
        string $meta,
        string $url,
        mixed $createdAt,
    ): array {
        return [
            'type' => $type,
            'tipo' => $type,
            'label' => $label,
            'tipo_label' => $label,
            'title' => $title,
            'titulo' => $title,
            'description' => $description,
            'descripcion' => $description,
            'meta' => $meta,
            'url' => $url,
            'created_at' => (string) $createdAt,
            'date' => $createdAt ? CarbonImmutable::parse($createdAt)->format('d/m/Y H:i') : '',
        ];
    }

    private function shouldSearch(string $category): bool
    {
        return array_key_exists($category, $this->availableCategories)
            && ($this->categoria === 'todos' || $this->categoria === $category);
    }

    private function normalizedDateTerm(string $search): ?string
    {
        foreach (['d/m/Y', 'd-m-Y'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat('!'.$format, $search);
                if ($date && $date->format($format) === $search) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
                // Continue with the next accepted presentation.
            }
        }

        return null;
    }

    private function numericTerm(string $search): ?float
    {
        return is_numeric($search) ? (float) $search : null;
    }

    private function dateLabel(mixed $date): string
    {
        if (! $date) {
            return 'Sin fecha';
        }

        return CarbonImmutable::parse($date)->format('d/m/Y');
    }

    private function categoryDescription(string $category): string
    {
        return match ($category) {
            'animal' => 'Arete, nombre, especie o raza',
            'engorde' => 'Código, nombre o estado del lote',
            'leche' => 'Fecha, turno, litros u observaciones',
            'queso' => 'Fecha, peso, unidades u observaciones',
            'monitoreo' => 'Animal, diagnóstico o tratamiento',
            'finanzas' => 'Concepto, categoría, fecha o monto',
            'auditoria' => 'Acción, módulo, detalle o usuario',
            default => '',
        };
    }

    public function render()
    {
        return view('livewire.buscador')->layout('layouts.app');
    }
}
