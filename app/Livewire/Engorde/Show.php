<?php

namespace App\Livewire\Engorde;

use App\Models\Animal;
use App\Models\EngordeAnimal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\PesajeEngorde;
use App\Support\EngordeReport;
use App\Traits\AuthorizesPermissions;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesPermissions;

    #[Locked]
    public $loteId;

    public $lote;

    // Modales
    public $showAddAnimalModal = false;

    public $showLogWeightModal = false;

    public $showReportModal = false;

    public array $reportColumns = EngordeReport::DEFAULT_COLUMNS;

    public $engordeEspecieId = '';

    public $engordeSearch = '';

    public array $selectedAnimals = [];

    public array $pesosIniciales = [];

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
        $filterSummary = 'Lote: '.$lots->first()->codigo.' | Estado: '.ucfirst($lots->first()->estado);
        $title = 'Reporte detallado del lote '.$lots->first()->codigo;
        $this->showReportModal = false;

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
            'title'
        ))->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'lote_engorde_'.Str::slug($lots->first()->codigo, '_').'_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    // Abrir modal de agregar animal
    public function openAddAnimalModal()
    {
        $this->authorizePermission('engorde', 'actualizar');
        abort_unless($this->lote->estado === 'activo', 422, 'El lote está cerrado.');
        $this->reset(['engordeEspecieId', 'engordeSearch', 'selectedAnimals', 'pesosIniciales']);
        $this->showAddAnimalModal = true;
    }

    public function toggleAnimalSelection(int $animalId): void
    {
        $this->authorizePermission('engorde', 'actualizar');

        if ($this->selectedAnimals[$animalId] ?? false) {
            unset($this->selectedAnimals[$animalId], $this->pesosIniciales[$animalId]);

            return;
        }

        $animal = $this->baseAvailableAnimalsQuery()->findOrFail($animalId);
        $this->selectedAnimals[$animalId] = true;
        $this->pesosIniciales[$animalId] = $animal->peso ?: '';
    }

    public function selectAllVisible(): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        abort_unless($this->engordeEspecieId, 422, 'Selecciona una especie.');

        $this->filteredAvailableAnimalsQuery()->limit(100)->get()->each(function (Animal $animal): void {
            $this->selectedAnimals[$animal->id] = true;
            $this->pesosIniciales[$animal->id] ??= $animal->peso ?: '';
        });
    }

    public function clearAnimalSelection(): void
    {
        $this->reset(['selectedAnimals', 'pesosIniciales']);
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

        $weightRules = $animalIds->mapWithKeys(fn ($id) => [
            "pesosIniciales.$id" => ['required', 'numeric', 'gt:0', 'max:999999.99'],
        ])->all();
        $this->validate($weightRules, [
            'pesosIniciales.*.required' => 'Registra peso inicial para cada animal seleccionado.',
            'pesosIniciales.*.gt' => 'Peso inicial debe ser mayor que cero.',
        ]);

        $savedIds = [];
        DB::transaction(function () use ($animalIds, &$savedIds): void {
            $lote = LoteEngorde::where('fundo_id', session('fundo_id'))
                ->lockForUpdate()
                ->findOrFail($this->loteId);
            if ($lote->estado !== 'activo') {
                throw ValidationException::withMessages(['selectedAnimals' => 'El lote está cerrado.']);
            }

            foreach ($animalIds->sort() as $animalId) {
                $animal = Animal::where('fundo_id', session('fundo_id'))
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->findOrFail($animalId);
                if (EngordeAnimal::where('animal_id', $animal->id)->where('estado', 'engorde_activo')->exists()) {
                    throw ValidationException::withMessages([
                        'selectedAnimals' => "$animal->arete ya pertenece a un lote activo.",
                    ]);
                }

                $engorde = EngordeAnimal::withTrashed()
                    ->where('lote_id', $lote->id)
                    ->where('animal_id', $animal->id)
                    ->first();
                if ($engorde?->trashed()) {
                    $engorde->restore();
                }
                $engorde ??= new EngordeAnimal;
                $weight = $this->pesosIniciales[$animalId];
                $engorde->fill([
                    'lote_id' => $lote->id,
                    'animal_id' => $animal->id,
                    'categoria' => null,
                    'peso_inicial' => $weight,
                    'peso_actual' => $weight,
                    'estado' => 'engorde_activo',
                    'fecha_ingreso' => now()->format('Y-m-d'),
                    'fecha_salida' => null,
                ])->save();
                $savedIds[] = $engorde->id;
            }
        }, attempts: 5);

        $this->showAddAnimalModal = false;
        $this->reset(['engordeEspecieId', 'engordeSearch', 'selectedAnimals', 'pesosIniciales']);
        $this->recentEngordeAnimalIds = $savedIds;
        $this->recentEngordeAction = 'created';
        $this->recentPesajeId = null;
        $this->loadLote();

        $this->dispatch('swal:modal', [
            'title' => 'Animales incorporados',
            'text' => $animalIds->count().' animal(es) agregados al lote correctamente.',
            'icon' => 'success',
        ]);
    }

    // Quitar animal del lote
    public function quitarAnimal($engordeAnimalId)
    {
        $this->authorizePermission('engorde', 'actualizar');

        $ea = EngordeAnimal::where('lote_id', $this->loteId)->findOrFail($engordeAnimalId);
        $ea->delete();

        $this->loadLote();
        $this->dispatch('swal:modal', [
            'title' => 'Ejemplar Retirado',
            'text' => 'El animal ha sido retirado del lote de engorde.',
            'icon' => 'success',
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

    // Registrar pesaje
    public function registrarPesaje()
    {
        $this->authorizePermission('engorde', 'actualizar');

        $this->validate([
            'nuevoPeso' => 'required|numeric|gt:0|max:999999.99',
            'fechaPesaje' => 'required|date|before_or_equal:today',
            'observacionesPesaje' => 'nullable|string',
        ]);

        $pesajeId = null;
        DB::transaction(function () use (&$pesajeId) {
            $lote = LoteEngorde::where('fundo_id', session('fundo_id'))->lockForUpdate()->findOrFail($this->loteId);
            if ($lote->estado !== 'activo') {
                throw ValidationException::withMessages(['nuevoPeso' => 'No puedes pesar animales en un lote cerrado.']);
            }

            $ea = EngordeAnimal::where('lote_id', $this->loteId)
                ->findOrFail($this->selectedEngordeAnimalId);

            if (CarbonImmutable::parse($this->fechaPesaje)->isBefore($ea->fecha_ingreso)) {
                throw ValidationException::withMessages([
                    'fechaPesaje' => 'Fecha de pesaje no puede ser anterior al ingreso al lote.',
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

        $this->showLogWeightModal = false;
        $this->recentEngordeAnimalIds = [(int) $this->selectedEngordeAnimalId];
        $this->recentEngordeAction = 'updated';
        $this->recentPesajeId = $pesajeId;
        $this->loadLote();

        $this->dispatch('swal:modal', [
            'title' => 'Peso Registrado',
            'text' => 'Control de peso actualizado correctamente.',
            'icon' => 'success',
        ]);
    }

    public function render()
    {
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
