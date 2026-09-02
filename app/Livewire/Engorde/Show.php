<?php

namespace App\Livewire\Engorde;

use App\Models\Animal;
use App\Models\CategoriaFinanciera;
use App\Models\EngordeAnimal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\Movimiento;
use App\Models\PesajeEngorde;
use App\Services\AnimalInventoryService;
use App\Support\EngordeReport;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasPdfPreviewModal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal, WithFileUploads;

    #[Locked]
    public $loteId;

    public $lote;

    // Modales
    public $showAddAnimalModal = false;

    public $showLogWeightModal = false;

    public $showReportModal = false;

    // Modales Liquidación / Venta
    public bool $showVenderLoteModal = false;

    public string $fechaVenta = '';

    public string $compradorVenta = '';

    public string $montoVenta = '';

    public string $observacionesVenta = '';

    public $comprobanteVenta = null;

    public array $animalesAVender = [];

    public array $preciosAnimales = [];

    // Modal Cierre de Lote
    public bool $showCerrarLoteModal = false;

    public string $fechaCierreLote = '';

    public string $observacionesCierreLote = '';

    public array $reportColumns = EngordeReport::DEFAULT_COLUMNS;

    public $engordeEspecieId = '';

    public $engordeSearch = '';

    public array $selectedAnimals = [];

    public array $pesosIniciales = [];

    public array $fechasIngreso = [];

    public string $fechaIngresoDefault = '';

    // Modal Editar Ingreso
    public bool $showEditIngresoModal = false;

    public ?int $editEngordeAnimalId = null;

    public string $editAnimalNombre = '';

    public string $editFechaIngreso = '';

    public string $editPesoInicial = '';

    public string $editObservaciones = '';

    #[Locked]
    public array $recentEngordeAnimalIds = [];

    #[Locked]
    public string $recentEngordeAction = '';

    #[Locked]
    public ?int $recentPesajeId = null;

    // Campos Registrar Pesaje
    public $selectedEngordeAnimalId = null;

    public $selectedAnimalName = '';

    public $nuevoPeso = '';

    public $fechaPesaje = '';

    public $observacionesPesaje = '';

    public function mount($id)
    {
        $this->loteId = $id;
        $this->loadLote();
        $this->fechaPesaje = now()->format('Y-m-d');
        $this->fechaIngresoDefault = now()->format('Y-m-d');
    }

    public function loadLote()
    {
        $this->lote = EngordeReport::loadLots((int) session('fundo_id'), [(int) $this->loteId])->first();
        abort_unless($this->lote, 404);

        if ($this->recentEngordeAnimalIds) {
            $recentIds = $this->recentEngordeAnimalIds;
            $this->lote->setRelation('animales', $this->lote->animales
                ->sortBy(fn ($item) => in_array($item->id, $recentIds, true) ? 0 : 1)
                ->values());
        }
    }

    public function openReportModal(): void
    {
        $this->authorizePermission('engorde', 'exportar');
        $this->resetValidation(['reportColumns', 'reportColumns.*']);
        $this->showReportModal = true;
    }

    public function closeReportModal(): void
    {
        $this->showReportModal = false;
        $this->resetValidation();
    }

    public function exportDetailedReport($columns = null)
    {
        $this->authorizePermission('engorde', 'exportar');

        if ($columns !== null) {
            $this->reportColumns = $columns;
        }

        $this->validate([
            'reportColumns' => ['required', 'array', 'min:1'],
            'reportColumns.*' => ['required', 'string', 'distinct', Rule::in(array_keys(EngordeReport::COLUMNS))],
        ], [
            'reportColumns.required' => 'Selecciona al menos una columna.',
            'reportColumns.min' => 'Selecciona al menos una columna.',
            'reportColumns.*.in' => 'La selección contiene una columna no válida.',
            'reportColumns.*.distinct' => 'No se pueden repetir columnas.',
        ]);

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $lots = EngordeReport::loadLots($fundoId, [(int) $this->loteId]);
        abort_unless($lots->count() === 1, 404);
        $selectedColumns = EngordeReport::normalizeColumns($this->reportColumns);
        $summary = EngordeReport::summarize($lots);
        $fundo = Fundo::findOrFail($fundoId);
        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $reportSummary = $summary['animals'].' animales. Peso inicial: '.number_format($summary['initial_weight'], 2).' kg. Peso de referencia: '.number_format($summary['reference_weight'], 2).' kg. Ganancia: '.number_format($summary['gain_kg'], 2).' kg.';
        $lot = $lots->first();
        $filterSummary = 'Lote: '.($lot?->codigo ?? 'N/A').' | Estado: '.ucfirst($lot?->estado ?? 'N/A');
        $title = 'Reporte detallado del lote '.($lot?->codigo ?? 'N/A');
        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.engorde-detallado', compact(
            'lots',
            'selectedColumns',
            'summary',
            'fundo',
            'generatedBy',
            'generatedAt',
            'administrators',
            'reportSummary',
            'filterSummary',
            'title',
            'includeSignatures',
            'scale'
        ))->setPaper('a4', 'landscape');

        // Solo cerrar el modal de opciones la PRIMERA vez (no al regenerar desde preview).
        if ($this->exportStep !== 'preview') {
            $this->showReportModal = false;
        }

        return $this->setPdfPreview(
            $pdf,
            'lote_engorde_'.Str::slug($lot?->codigo ?? 'sin-codigo', '_').'_'.now()->format('Ymd_His').'.pdf',
            $title,
            $summary['animals']
        );
    }

    // Abrir modal de agregar animal
    public function openAddAnimalModal()
    {
        $this->authorizePermission('engorde', 'actualizar');
        abort_unless($this->lote->estado === 'activo', 422, 'El lote está cerrado.');
        $this->fechaIngresoDefault = now()->format('Y-m-d');
        $this->reset(['engordeEspecieId', 'engordeSearch', 'selectedAnimals', 'pesosIniciales', 'fechasIngreso']);
        $this->showAddAnimalModal = true;
    }

    public function toggleAnimalSelection(int $animalId): void
    {
        $this->authorizePermission('engorde', 'actualizar');

        if ($this->selectedAnimals[$animalId] ?? false) {
            unset($this->selectedAnimals[$animalId], $this->pesosIniciales[$animalId], $this->fechasIngreso[$animalId]);

            return;
        }

        $animal = $this->baseAvailableAnimalsQuery()->findOrFail($animalId);
        $this->selectedAnimals[$animalId] = true;
        $this->pesosIniciales[$animalId] = $animal->peso ?: '';
        $this->fechasIngreso[$animalId] = $this->fechaIngresoDefault ?: now()->format('Y-m-d');
    }

    public function selectAllVisible(): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        abort_unless($this->engordeEspecieId, 422, 'Selecciona una especie.');

        $defaultDate = $this->fechaIngresoDefault ?: now()->format('Y-m-d');
        $this->filteredAvailableAnimalsQuery()->limit(100)->get()->each(function (Animal $animal) use ($defaultDate): void {
            $this->selectedAnimals[$animal->id] = true;
            $this->pesosIniciales[$animal->id] ??= $animal->peso ?: '';
            $this->fechasIngreso[$animal->id] ??= $defaultDate;
        });
    }

    public function updatedFechaIngresoDefault($value): void
    {
        if (! $value) {
            return;
        }
        foreach (array_keys($this->selectedAnimals) as $id) {
            $this->fechasIngreso[$id] = $value;
        }
    }

    public function clearAnimalSelection(): void
    {
        $this->reset(['selectedAnimals', 'pesosIniciales', 'fechasIngreso']);
    }

    public function clearRecentEngordeRows(): void
    {
        $this->recentEngordeAnimalIds = [];
        $this->recentEngordeAction = '';
        $this->recentPesajeId = null;
        $this->loadLote();
    }

    public function agregarAnimales()
    {
        $this->authorizePermission('engorde', 'actualizar');
        $animalIds = collect($this->selectedAnimals)
            ->filter()
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($animalIds->isEmpty()) {
            throw ValidationException::withMessages([
                'selectedAnimals' => 'Selecciona al menos un animal.',
            ]);
        }

        $defaultDate = $this->fechaIngresoDefault ?: now()->format('Y-m-d');
        foreach ($animalIds as $id) {
            $this->fechasIngreso[$id] ??= $defaultDate;
        }

        $rules = [];
        $messages = [];
        foreach ($animalIds as $id) {
            $rules["pesosIniciales.$id"] = ['required', 'numeric', 'gt:0', 'max:999999.99'];
            $rules["fechasIngreso.$id"] = ['required', 'date', 'before_or_equal:today'];
            $messages["pesosIniciales.$id.required"] = 'Registra peso inicial para cada animal seleccionado.';
            $messages["pesosIniciales.$id.gt"] = 'Peso inicial debe ser mayor que cero.';
            $messages["fechasIngreso.$id.required"] = 'Registra la fecha de ingreso.';
            $messages["fechasIngreso.$id.date"] = 'Fecha de ingreso no válida.';
            $messages["fechasIngreso.$id.before_or_equal"] = 'La fecha de ingreso no puede ser futura.';
        }
        $this->validate($rules, $messages);

        $savedIds = [];
        DB::transaction(function () use ($animalIds, &$savedIds): void {
            $lote = LoteEngorde::where('fundo_id', session('fundo_id'))
                ->lockForUpdate()
                ->findOrFail($this->loteId);
            if ($lote->estado !== 'activo') {
                throw ValidationException::withMessages(['selectedAnimals' => 'El lote está cerrado.']);
            }

            $animales = Animal::where('fundo_id', session('fundo_id'))
                ->where('activo', true)
                ->whereIn('id', $animalIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $alreadyActive = EngordeAnimal::whereIn('animal_id', $animalIds)
                ->where('estado', 'engorde_activo')
                ->first();
            if ($alreadyActive) {
                $animal = $animales->get($alreadyActive->animal_id);
                throw ValidationException::withMessages([
                    'selectedAnimals' => ($animal?->arete ?? 'El animal').' ya pertenece a un lote activo.',
                ]);
            }

            $loteInicio = $lote->fecha_inicio->startOfDay();
            $loteFin = $lote->fecha_fin?->endOfDay();

            foreach ($animalIds as $animalId) {
                $rawFecha = $this->fechasIngreso[$animalId] ?? now()->format('Y-m-d');
                $fechaParsed = CarbonImmutable::parse($rawFecha);

                if ($fechaParsed->isBefore($loteInicio)) {
                    throw ValidationException::withMessages([
                        "fechasIngreso.$animalId" => 'La fecha de ingreso no puede ser anterior al inicio del lote ('.$lote->fecha_inicio->format('d/m/Y').').',
                    ]);
                }

                if ($loteFin && $fechaParsed->isAfter($loteFin)) {
                    throw ValidationException::withMessages([
                        "fechasIngreso.$animalId" => 'La fecha de ingreso no puede ser posterior al cierre del lote ('.$lote->fecha_fin->format('d/m/Y').').',
                    ]);
                }

                $animal = $animales->get($animalId);
                if ($animal?->fecha_nacimiento && $fechaParsed->isBefore($animal->fecha_nacimiento->startOfDay())) {
                    throw ValidationException::withMessages([
                        "fechasIngreso.$animalId" => 'La fecha de ingreso no puede ser anterior al nacimiento del animal ('.$animal->fecha_nacimiento->format('d/m/Y').').',
                    ]);
                }

                if ($animal?->fecha_alta && $animal->fecha_alta->isAfter($fechaParsed->startOfDay())) {
                    $animal->update(['fecha_alta' => $fechaParsed->toDateString()]);
                }
            }

            $existing = EngordeAnimal::withTrashed()
                ->where('lote_id', $lote->id)
                ->whereIn('animal_id', $animalIds)
                ->get()
                ->keyBy('animal_id');

            foreach ($animalIds->sort() as $animalId) {
                $weight = $this->pesosIniciales[$animalId];
                $fechaIngreso = $this->fechasIngreso[$animalId] ?? now()->format('Y-m-d');
                $prev = $existing->get($animalId);

                if ($prev) {
                    if ($prev->trashed()) {
                        $prev->restore();
                    }
                    $prev->forceFill([
                        'peso_inicial' => $weight,
                        'peso_actual' => $weight,
                        'estado' => 'engorde_activo',
                        'fecha_ingreso' => $fechaIngreso,
                        'fecha_salida' => null,
                    ])->save();
                    continue;
                }

                (new EngordeAnimal)->fill([
                    'lote_id' => $lote->id,
                    'animal_id' => $animalId,
                    'categoria' => null,
                    'peso_inicial' => $weight,
                    'peso_actual' => $weight,
                    'estado' => 'engorde_activo',
                    'fecha_ingreso' => $fechaIngreso,
                    'fecha_salida' => null,
                ])->save();
            }

            $savedIds = EngordeAnimal::where('lote_id', $lote->id)
                ->whereIn('animal_id', $animalIds)
                ->pluck('id', 'animal_id')
                ->all();
            $savedIds = $animalIds->sort()->map(fn ($id) => $savedIds[$id] ?? null)->filter()->all();
        }, attempts: 5);

        $this->showAddAnimalModal = false;
        $this->reset(['engordeEspecieId', 'engordeSearch', 'selectedAnimals', 'pesosIniciales', 'fechasIngreso']);
        $this->recentEngordeAnimalIds = $savedIds;
        $this->recentEngordeAction = 'created';
        $this->recentPesajeId = null;
        $this->loadLote();

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Animales incorporados',
            'text' => $animalIds->count().' animal(es) agregados al lote correctamente.',
        ]);
    }

    public function openEditIngresoModal(int $engordeAnimalId): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $ea = EngordeAnimal::with(['animal', 'lote'])->where('lote_id', $this->loteId)->findOrFail($engordeAnimalId);
        $this->editEngordeAnimalId = $ea->id;
        $this->editAnimalNombre = ($ea->animal->nombre ? $ea->animal->nombre.' ('.$ea->animal->arete.')' : $ea->animal->arete);
        $this->editFechaIngreso = $ea->fecha_ingreso ? $ea->fecha_ingreso->format('Y-m-d') : now()->format('Y-m-d');
        $this->editPesoInicial = (string) $ea->peso_inicial;
        $this->editObservaciones = $ea->observaciones ?? '';
        $this->resetValidation();
        $this->showEditIngresoModal = true;
    }

    public function closeEditIngresoModal(): void
    {
        $this->showEditIngresoModal = false;
        $this->reset(['editEngordeAnimalId', 'editAnimalNombre', 'editFechaIngreso', 'editPesoInicial', 'editObservaciones']);
        $this->resetValidation();
    }

    public function actualizarIngreso(): void
    {
        $this->authorizePermission('engorde', 'actualizar');

        $this->validate([
            'editPesoInicial' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
            'editFechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            'editObservaciones' => ['nullable', 'string', 'max:255'],
        ], [
            'editPesoInicial.required' => 'Ingresa el peso inicial de engorde.',
            'editPesoInicial.numeric' => 'El peso inicial debe ser un número válido.',
            'editPesoInicial.gt' => 'El peso inicial debe ser mayor a 0 kg.',
            'editFechaIngreso.required' => 'Selecciona la fecha de ingreso.',
            'editFechaIngreso.date' => 'Ingresa una fecha de ingreso válida.',
            'editFechaIngreso.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
            'editObservaciones.max' => 'Las observaciones no pueden superar los 255 caracteres.',
        ]);

        DB::transaction(function (): void {
            $ea = EngordeAnimal::with(['lote', 'animal', 'pesajes' => fn ($q) => $q->orderBy('fecha')->orderBy('id')])
                ->where('lote_id', $this->loteId)
                ->lockForUpdate()
                ->findOrFail($this->editEngordeAnimalId);

            $lote = $ea->lote;
            if ($lote->estado !== 'activo') {
                throw ValidationException::withMessages(['editFechaIngreso' => 'El lote se encuentra cerrado.']);
            }

            $parsedDate = CarbonImmutable::parse($this->editFechaIngreso);

            // Validar contra fecha_inicio del lote
            if ($parsedDate->isBefore($lote->fecha_inicio->startOfDay())) {
                throw ValidationException::withMessages([
                    'editFechaIngreso' => 'La fecha de ingreso no puede ser anterior al inicio del lote ('.$lote->fecha_inicio->format('d/m/Y').').',
                ]);
            }

            // Validar contra fecha_fin del lote si existe
            if ($lote->fecha_fin && $parsedDate->isAfter($lote->fecha_fin->endOfDay())) {
                throw ValidationException::withMessages([
                    'editFechaIngreso' => 'La fecha de ingreso no puede ser posterior al cierre del lote ('.$lote->fecha_fin->format('d/m/Y').').',
                ]);
            }

            // Validar contra fecha_nacimiento del animal si existe
            if ($ea->animal?->fecha_nacimiento && $parsedDate->isBefore($ea->animal->fecha_nacimiento->startOfDay())) {
                throw ValidationException::withMessages([
                    'editFechaIngreso' => 'La fecha de ingreso no puede ser anterior al nacimiento del animal ('.$ea->animal->fecha_nacimiento->format('d/m/Y').').',
                ]);
            }

            if ($ea->animal?->fecha_alta && $ea->animal->fecha_alta->isAfter($parsedDate->startOfDay())) {
                $ea->animal->update(['fecha_alta' => $parsedDate->toDateString()]);
            }

            // Validar contra el primer pesaje registrado si tiene pesajes
            $firstPesaje = $ea->pesajes->first();
            if ($firstPesaje && $parsedDate->isAfter($firstPesaje->fecha->startOfDay())) {
                throw ValidationException::withMessages([
                    'editFechaIngreso' => 'La fecha de ingreso no puede ser posterior al primer control de pesaje registrado ('.$firstPesaje->fecha->format('d/m/Y').').',
                ]);
            }

            $hasPesajes = $ea->pesajes->isNotEmpty();
            $ea->fecha_ingreso = $this->editFechaIngreso;
            $ea->peso_inicial = $this->editPesoInicial;
            $ea->observaciones = $this->editObservaciones ?: null;

            if (! $hasPesajes) {
                $ea->peso_actual = $this->editPesoInicial;
            }

            $ea->save();
        });

        $this->showEditIngresoModal = false;
        $this->recentEngordeAnimalIds = [(int) $this->editEngordeAnimalId];
        $this->recentEngordeAction = 'updated';
        $this->loadLote();

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Datos de Ingreso Actualizados',
            'text' => 'La fecha y peso de ingreso se actualizaron correctamente.',
        ]);
    }

    public function openVenderLoteModal(): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $this->authorizePermission('finanzas', 'crear');
        abort_unless($this->lote->estado === 'activo', 422, 'El lote ya se encuentra cerrado.');

        $activeAnimals = $this->lote->animales->filter(fn ($ea) => $ea->estado === 'engorde_activo');
        abort_if($activeAnimals->isEmpty(), 422, 'No hay animales activos en este lote para vender.');

        $this->fechaVenta = now()->format('Y-m-d');
        $this->compradorVenta = '';
        $this->montoVenta = '';
        $this->observacionesVenta = '';
        $this->comprobanteVenta = null;
        $this->animalesAVender = $activeAnimals->pluck('animal_id')->mapWithKeys(fn ($id) => [$id => true])->all();
        $this->preciosAnimales = $activeAnimals->pluck('animal_id')->mapWithKeys(fn ($id) => [$id => ''])->all();
        $this->resetValidation();
        $this->showVenderLoteModal = true;
    }

    public function closeVenderLoteModal(): void
    {
        $this->showVenderLoteModal = false;
        $this->reset(['fechaVenta', 'compradorVenta', 'montoVenta', 'observacionesVenta', 'comprobanteVenta', 'animalesAVender', 'preciosAnimales']);
        $this->resetValidation();
    }

    public function updatedPreciosAnimales(): void
    {
        $this->recalcularMontoVentaDesdePrecios();
    }

    public function updatedAnimalesAVender(): void
    {
        $this->recalcularMontoVentaDesdePrecios();
    }

    private function recalcularMontoVentaDesdePrecios(): void
    {
        $selectedIds = collect($this->animalesAVender)->filter()->keys();
        $total = 0;
        $hasAnyPrice = false;

        foreach ($selectedIds as $id) {
            $price = $this->preciosAnimales[$id] ?? null;
            if ($price !== null && $price !== '' && is_numeric($price)) {
                $total += (float) $price;
                $hasAnyPrice = true;
            }
        }

        if ($hasAnyPrice && $total > 0) {
            $this->montoVenta = number_format($total, 2, '.', '');
        }
    }

    public function distribuirMontoTotalEquitativo(): void
    {
        $selectedIds = collect($this->animalesAVender)->filter()->keys()->values();
        $count = $selectedIds->count();
        if ($count === 0 || ! is_numeric($this->montoVenta) || (float) $this->montoVenta <= 0) {
            return;
        }

        $total = (float) $this->montoVenta;
        $unitPrice = round($total / $count, 2);

        $accumulated = 0;
        foreach ($selectedIds as $index => $id) {
            if ($index === $count - 1) {
                $price = round($total - $accumulated, 2);
            } else {
                $price = $unitPrice;
                $accumulated += $price;
            }
            $this->preciosAnimales[$id] = number_format($price, 2, '.', '');
        }
    }

    public function distribuirMontoPorPeso(): void
    {
        if (! $this->lote) {
            return;
        }

        $selectedIds = collect($this->animalesAVender)->filter()->keys()->values();
        if ($selectedIds->isEmpty() || ! is_numeric($this->montoVenta) || (float) $this->montoVenta <= 0) {
            return;
        }

        $activeRecords = $this->lote->animales
            ->filter(fn ($ea) => $selectedIds->contains($ea->animal_id));

        $totalWeight = $activeRecords->sum(fn ($ea) => (float) ($ea->peso_actual ?: $ea->peso_inicial));
        if ($totalWeight <= 0) {
            $this->distribuirMontoTotalEquitativo();
            return;
        }

        $totalAmount = (float) $this->montoVenta;
        $pricePerKg = $totalAmount / $totalWeight;
        $accumulated = 0;
        $count = $activeRecords->count();
        $i = 0;

        foreach ($activeRecords as $ea) {
            $weight = (float) ($ea->peso_actual ?: $ea->peso_inicial);
            $i++;
            if ($i === $count) {
                $price = round($totalAmount - $accumulated, 2);
            } else {
                $price = round($weight * $pricePerKg, 2);
                $accumulated += $price;
            }
            $this->preciosAnimales[$ea->animal_id] = number_format($price, 2, '.', '');
        }
    }

    public function limpiarComprobanteVenta(): void
    {
        $this->comprobanteVenta = null;
    }

    public function liquidarVentaLote(): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $this->authorizePermission('finanzas', 'crear');

        $animalIds = collect($this->animalesAVender)->filter()->keys()->map(fn ($id) => (int) $id)->values()->all();

        if (empty($animalIds)) {
            throw ValidationException::withMessages([
                'animalesAVender' => 'Selecciona al menos un animal para la venta.',
            ]);
        }

        $this->preciosAnimales = collect($this->preciosAnimales)
            ->map(fn ($p) => ($p === '' || $p === null) ? null : $p)
            ->all();

        $this->validate([
            'fechaVenta' => ['required', 'date', 'before_or_equal:today'],
            'compradorVenta' => ['nullable', 'string', 'max:255'],
            'montoVenta' => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'preciosAnimales.*' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'observacionesVenta' => ['nullable', 'string', 'max:1000'],
            'comprobanteVenta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:25600'],
        ], [
            'fechaVenta.required' => 'Selecciona la fecha de la venta.',
            'fechaVenta.before_or_equal' => 'La fecha de venta no puede ser futura.',
            'montoVenta.required' => 'Ingresa el monto total de la venta.',
            'montoVenta.numeric' => 'El monto debe ser un valor numérico.',
            'montoVenta.gt' => 'El monto debe ser mayor a 0.',
            'comprobanteVenta.mimes' => 'El comprobante debe ser una imagen (JPG, PNG, WebP) o un archivo PDF.',
            'comprobanteVenta.max' => 'El archivo no debe superar 25 MB.',
        ]);

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        DB::transaction(function () use ($animalIds, $fundoId): void {
            $lote = LoteEngorde::where('fundo_id', $fundoId)
                ->with(['animales.animal'])
                ->lockForUpdate()
                ->findOrFail($this->loteId);

            if ($lote->estado !== 'activo') {
                throw ValidationException::withMessages(['fechaVenta' => 'El lote ya se encuentra cerrado.']);
            }

            if (CarbonImmutable::parse($this->fechaVenta)->isBefore($lote->fecha_inicio->startOfDay())) {
                throw ValidationException::withMessages([
                    'fechaVenta' => 'La fecha de venta no puede ser anterior al inicio del lote ('.$lote->fecha_inicio->format('d/m/Y').').',
                ]);
            }

            // Categoría de Finanzas para Venta de Animales
            $categoria = CategoriaFinanciera::firstOrCreate(
                ['fundo_id' => $fundoId, 'tipo' => 'ingreso', 'nombre' => 'Venta de Animales'],
                ['activo' => true]
            );

            $comprobantePath = null;
            if ($this->comprobanteVenta) {
                $isImage = str_starts_with((string) $this->comprobanteVenta->getMimeType(), 'image/');
                $comprobantePath = $isImage
                    ? ImageOptimizer::store(
                        $this->comprobanteVenta,
                        'comprobantes',
                        'comprobanteVenta',
                        1400,
                        900 * 1024,
                        'local'
                    )
                    : $this->comprobanteVenta->store('comprobantes', 'local');
            }

            // Build detailed breakdown
            $breakdown = [];
            foreach ($lote->animales->whereIn('animal_id', $animalIds) as $ea) {
                $code = $ea->animal?->arete ?: "ID {$ea->animal_id}";
                $p = $this->preciosAnimales[$ea->animal_id] ?? null;
                $breakdown[] = $p !== null && $p !== '' && is_numeric($p)
                    ? "{$code}: S/ " . number_format((float) $p, 2)
                    : $code;
            }

            $detalleTexto = ! empty($breakdown) ? ' (' . implode(', ', $breakdown) . ')' : '';
            $descripcion = '[VENTA LOTE ENGORDE: '.$lote->codigo.']' . $detalleTexto . ($this->observacionesVenta ? ' — ' . trim($this->observacionesVenta) : ' Venta y liquidación de animales en engorde.');

            // 1. Crear Asiento Contable en Finanzas
            $movimiento = Movimiento::create([
                'fundo_id' => $fundoId,
                'tipo' => 'ingreso',
                'categoria_id' => $categoria->id,
                'monto' => $this->montoVenta,
                'moneda' => 'PEN',
                'beneficiario' => trim($this->compradorVenta) ?: null,
                'proposito' => 'comercial',
                'fecha' => $this->fechaVenta,
                'descripcion' => $descripcion,
                'comprobante_ruta' => $comprobantePath,
            ]);

            // 2. Dar de baja los animales por venta y vincularlos al movimiento
            app(AnimalInventoryService::class)->linkSale(
                $movimiento,
                $animalIds,
                trim($this->compradorVenta) ?: 'Sin especificar',
                $this->preciosAnimales
            );

            // 3. Si todos los animales activos del lote fueron vendidos, cerrar el lote
            $remainingActive = EngordeAnimal::where('lote_id', $lote->id)
                ->where('estado', 'engorde_activo')
                ->whereNotIn('animal_id', $animalIds)
                ->exists();

            if (! $remainingActive) {
                $lote->update([
                    'estado' => 'cerrado',
                    'fecha_fin' => $this->fechaVenta,
                ]);
            }
        });

        $this->showVenderLoteModal = false;
        $this->reset(['fechaVenta', 'compradorVenta', 'montoVenta', 'observacionesVenta', 'comprobanteVenta', 'animalesAVender', 'preciosAnimales']);
        $this->loadLote();

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Venta Registrada en Finanzas',
            'text' => 'Se registraron los ingresos y se actualizó el estado de los animales vendidos.',
        ]);
    }

    public function openCerrarLoteModal(): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        abort_unless($this->lote->estado === 'activo', 422, 'El lote ya está cerrado.');
        $this->fechaCierreLote = now()->format('Y-m-d');
        $this->observacionesCierreLote = '';
        $this->resetValidation();
        $this->showCerrarLoteModal = true;
    }

    public function closeCerrarLoteModal(): void
    {
        $this->showCerrarLoteModal = false;
        $this->reset(['fechaCierreLote', 'observacionesCierreLote']);
        $this->resetValidation();
    }

    public function finalizarLote(): void
    {
        $this->authorizePermission('engorde', 'actualizar');

        $this->validate([
            'fechaCierreLote' => ['required', 'date', 'before_or_equal:today'],
            'observacionesCierreLote' => ['nullable', 'string', 'max:1000'],
        ], [
            'fechaCierreLote.required' => 'Selecciona la fecha de cierre.',
            'fechaCierreLote.before_or_equal' => 'La fecha de cierre no puede ser futura.',
        ]);

        $fundoId = (int) session('fundo_id');
        DB::transaction(function () use ($fundoId): void {
            $lote = LoteEngorde::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($this->loteId);
            if (CarbonImmutable::parse($this->fechaCierreLote)->isBefore($lote->fecha_inicio->startOfDay())) {
                throw ValidationException::withMessages([
                    'fechaCierreLote' => 'La fecha de cierre no puede ser anterior al inicio del lote ('.$lote->fecha_inicio->format('d/m/Y').').',
                ]);
            }

            $lote->update([
                'estado' => 'cerrado',
                'fecha_fin' => $this->fechaCierreLote,
                'observaciones' => $this->observacionesCierreLote
                    ? ($lote->observaciones ? $lote->observaciones."\n[CIERRE]: ".$this->observacionesCierreLote : $this->observacionesCierreLote)
                    : $lote->observaciones,
            ]);

            // Fijar fecha_salida en animales activos
            EngordeAnimal::where('lote_id', $lote->id)
                ->where('estado', 'engorde_activo')
                ->whereNull('fecha_salida')
                ->update(['fecha_salida' => $this->fechaCierreLote]);
        });

        $this->showCerrarLoteModal = false;
        $this->reset(['fechaCierreLote', 'observacionesCierreLote']);
        $this->loadLote();

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Lote Finalizado',
            'text' => 'El lote de engorde ha sido cerrado exitosamente.',
        ]);
    }

    public function reabrirLote(): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        abort_unless($this->lote->estado === 'cerrado', 422, 'El lote no está cerrado.');

        $fundoId = (int) session('fundo_id');
        DB::transaction(function () use ($fundoId): void {
            $lote = LoteEngorde::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($this->loteId);
            $lote->update([
                'estado' => 'activo',
                'fecha_fin' => null,
            ]);

            // Restaurar fecha_salida para animales que sigan activos
            EngordeAnimal::where('lote_id', $lote->id)
                ->where('estado', 'engorde_activo')
                ->update(['fecha_salida' => null]);
        });

        $this->loadLote();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Lote Reabierto',
            'text' => 'El lote vuelve a estar en estado activo para nuevos controles.',
        ]);
    }

    protected $listeners = [
        'confirmarQuitarAnimal' => 'quitarAnimal',
    ];

    // Solicitar confirmación para quitar animal del lote
    public function solicitarQuitarAnimal($engordeAnimalId): void
    {
        $this->authorizePermission('engorde', 'actualizar');

        $ea = EngordeAnimal::with('animal')->where('lote_id', $this->loteId)->findOrFail($engordeAnimalId);
        $animalNombre = $ea->animal
            ? ($ea->animal->nombre ? $ea->animal->nombre.' ('.$ea->animal->arete.')' : $ea->animal->arete)
            : 'este ejemplar';

        $this->dispatch('swal:confirm', [
            'title' => '¿Estás seguro?',
            'text' => 'Se retirará al animal '.$animalNombre.' de este lote de engorde.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, retirar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarQuitarAnimal',
            'id' => $engordeAnimalId,
        ]);
    }

    // Quitar animal del lote
    public function quitarAnimal($id = null): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;

        if (! $targetId) {
            return;
        }

        $ea = EngordeAnimal::where('lote_id', $this->loteId)->findOrFail($targetId);
        $ea->delete();

        $this->loadLote();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Ejemplar Retirado',
            'text' => 'El animal ha sido retirado del lote de engorde exitosamente.',
        ]);
    }

    // Abrir modal de pesaje
    public function openLogWeightModal($engordeAnimalId)
    {
        $this->authorizePermission('engorde', 'actualizar');
        $ea = EngordeAnimal::with(['animal', 'ultimoPesaje'])->where('lote_id', $this->loteId)->findOrFail($engordeAnimalId);
        $this->selectedEngordeAnimalId = $engordeAnimalId;
        $this->selectedAnimalName = ($ea->animal->nombre ?: 'Sin Nombre').' ('.$ea->animal->arete.')';
        $this->nuevoPeso = $ea->reportMetrics()['reference_weight'];
        $this->fechaPesaje = now()->format('Y-m-d');
        $this->observacionesPesaje = '';
        $this->showLogWeightModal = true;
    }

    public function registrarPesaje()
    {
        $this->authorizePermission('engorde', 'actualizar');

        $this->validate([
            'nuevoPeso' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
            'fechaPesaje' => ['required', 'date', 'before_or_equal:today'],
            'observacionesPesaje' => ['nullable', 'string', 'max:255'],
        ], [
            'nuevoPeso.required' => 'Ingresa el nuevo peso del animal en kilogramos.',
            'nuevoPeso.numeric' => 'El peso debe ser un valor numérico válido.',
            'nuevoPeso.gt' => 'El peso registrado debe ser mayor a 0 kg.',
            'nuevoPeso.max' => 'El peso ingresado supera el límite permitido.',
            'fechaPesaje.required' => 'Selecciona la fecha del pesaje.',
            'fechaPesaje.date' => 'Ingresa una fecha de pesaje válida.',
            'fechaPesaje.before_or_equal' => 'La fecha del pesaje no puede ser futura.',
            'observacionesPesaje.max' => 'Las observaciones no pueden superar los 255 caracteres.',
        ]);

        $pesajeId = null;
        try {
            DB::transaction(function () use (&$pesajeId) {
                $lote = LoteEngorde::where('fundo_id', session('fundo_id'))->lockForUpdate()->findOrFail($this->loteId);
                if ($lote->estado !== 'activo') {
                    throw ValidationException::withMessages(['nuevoPeso' => 'El lote se encuentra cerrado.']);
                }

                $ea = EngordeAnimal::where('lote_id', $this->loteId)
                    ->findOrFail($this->selectedEngordeAnimalId);

                if (CarbonImmutable::parse($this->fechaPesaje)->isBefore($ea->fecha_ingreso)) {
                    throw ValidationException::withMessages([
                        'fechaPesaje' => 'La fecha de pesaje no puede ser anterior a su fecha de ingreso ('.$ea->fecha_ingreso->format('d/m/Y').').',
                    ]);
                }

                // Pre-check para notificar en español si ya existe registro en la misma fecha
                $alreadyExists = PesajeEngorde::where('engorde_animal_id', $ea->id)
                    ->where('fecha', $this->fechaPesaje)
                    ->exists();

                if ($alreadyExists) {
                    throw ValidationException::withMessages([
                        'fechaPesaje' => 'Ya existe un pesaje registrado para este animal en la fecha '.$this->fechaPesaje.'.',
                    ]);
                }

                $pesaje = PesajeEngorde::create([
                    'engorde_animal_id' => $ea->id,
                    'fecha' => $this->fechaPesaje,
                    'peso_kg' => $this->nuevoPeso,
                    'observaciones' => $this->observacionesPesaje ?: null,
                ]);
                $pesajeId = $pesaje->id;

                $latestWeight = PesajeEngorde::where('engorde_animal_id', $ea->id)
                    ->orderByDesc('fecha')
                    ->orderByDesc('id')
                    ->firstOrFail();
                $ea->update(['peso_actual' => $latestWeight->peso_kg]);
                $ea->animal()->first()?->update(['peso' => $latestWeight->peso_kg]);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '19') || str_contains($e->getMessage(), 'UNIQUE')) {
                throw ValidationException::withMessages([
                    'fechaPesaje' => 'Ya existe un control de peso para este animal registrado en esta fecha.',
                ]);
            }
            throw $e;
        }

        $this->showLogWeightModal = false;
        $this->recentEngordeAnimalIds = [(int) $this->selectedEngordeAnimalId];
        $this->recentEngordeAction = 'updated';
        $this->recentPesajeId = $pesajeId;
        $this->loadLote();

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Peso Registrado',
            'text' => 'Control de peso actualizado correctamente.',
        ]);
    }

    public function closeAddAnimalModal(): void
    {
        $this->showAddAnimalModal = false;
        $this->reset(['engordeEspecieId', 'engordeSearch', 'selectedAnimals', 'pesosIniciales']);
        $this->resetValidation();
    }

    public function closeLogWeightModal(): void
    {
        $this->showLogWeightModal = false;
        $this->reset(['selectedEngordeAnimalId', 'selectedAnimalName', 'nuevoPeso', 'observacionesPesaje']);
        $this->resetValidation();
    }

    public function render()
    {
        $this->loadLote();

        $availableSpeciesIds = $this->baseAvailableAnimalsQuery()->select('especie_id')->distinct();
        $especiesDisponibles = Especie::where('activo', true)
            ->whereIn('id', $availableSpeciesIds)
            ->orderBy('nombre')
            ->get();
        $animalesDisponibles = $this->engordeEspecieId
            ? $this->filteredAvailableAnimalsQuery()->limit(100)->get()
            : collect();
        $reportAvailableColumns = EngordeReport::COLUMNS;
        $loteSummary = EngordeReport::summarize(collect([$this->lote]));

        return view('livewire.engorde.show', compact(
            'animalesDisponibles',
            'especiesDisponibles',
            'reportAvailableColumns',
            'loteSummary'
        ))
            ->layout('layouts.app');
    }

    private function baseAvailableAnimalsQuery()
    {
        return Animal::query()
            ->where('fundo_id', session('fundo_id'))
            ->with(['especie', 'raza'])
            ->where('activo', true)
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('engorde_animales')
                    ->whereColumn('engorde_animales.animal_id', 'animales.id')
                    ->where('engorde_animales.estado', 'engorde_activo')
                    ->whereNull('engorde_animales.deleted_at');
            });
    }

    private function filteredAvailableAnimalsQuery()
    {
        return $this->baseAvailableAnimalsQuery()
            ->when($this->engordeEspecieId, fn ($query) => $query->where('especie_id', $this->engordeEspecieId))
            ->when($this->engordeSearch, fn ($query) => $query->where(function ($search) {
                $search->where('arete', 'like', '%'.$this->engordeSearch.'%')
                    ->orWhere('nombre', 'like', '%'.$this->engordeSearch.'%');
            }))
            ->orderBy('arete');
    }
}
