<?php

namespace App\Livewire\Monitoreo;

use App\Models\AlertaProgramada;
use App\Models\Animal;
use App\Models\Medicamento;
use App\Models\SanidadRegistro;
use App\Models\TratamientoDosis;
use App\Services\MedicamentoInventoryService;
use App\Support\ImageFrame;
use App\Traits\AuthorizesPermissions;
use App\Traits\HandlesRecordPhotos;
use App\Traits\PublishesRecentRecord;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class SanidadForm extends Component
{
    use AuthorizesPermissions, HandlesRecordPhotos, PublishesRecentRecord, WithFileUploads;

    /**
     * Motivos reconocibles en campo. La clasificación técnica se deriva de aquí;
     * el operador no tiene que conocer categorías internas.
     */
    private const MOTIVOS = [
        'problema' => [
            'label' => 'Problema observado',
            'items' => [
                'signos_generales' => ['label' => 'Fiebre, decaimiento o falta de apetito', 'category' => 'enfermedad', 'subtype' => 'sistemica'],
                'respiratorio' => ['label' => 'Tos, secreción nasal o dificultad respiratoria', 'category' => 'enfermedad', 'subtype' => 'respiratoria'],
                'digestivo' => ['label' => 'Diarrea, timpanismo u otro problema digestivo', 'category' => 'enfermedad', 'subtype' => 'digestiva'],
                'ubre_leche' => ['label' => 'Ubre o leche anormal', 'category' => 'enfermedad', 'subtype' => 'mamaria'],
                'reproductivo' => ['label' => 'Problema reproductivo o posparto', 'category' => 'enfermedad', 'subtype' => 'reproductiva'],
                'locomotor' => ['label' => 'Cojera, pezuña o dificultad para caminar', 'category' => 'lesion', 'subtype' => 'cojera', 'location' => true],
                'herida_piel' => ['label' => 'Herida, inflamación o problema de piel', 'category' => 'lesion', 'subtype' => 'herida_trauma', 'location' => true],
                'ocular' => ['label' => 'Ojo, visión o secreción ocular', 'category' => 'enfermedad', 'subtype' => 'ocular'],
                'metabolico' => ['label' => 'Pérdida de condición o problema metabólico', 'category' => 'enfermedad', 'subtype' => 'metabolica'],
                'otro_signo' => ['label' => 'Otro signo o problema', 'category' => 'otro', 'subtype' => 'otro'],
            ],
        ],
        'aplicacion' => [
            'label' => 'Aplicación de producto',
            'items' => [
                'vacunacion' => ['label' => 'Vacunación', 'category' => 'vacunacion', 'subtype' => 'rutina', 'plan' => true],
                'parasitos_internos' => ['label' => 'Control de parásitos internos', 'category' => 'parasitos', 'subtype' => 'internos', 'plan' => true],
                'parasitos_externos' => ['label' => 'Control de parásitos externos', 'category' => 'parasitos', 'subtype' => 'externos', 'plan' => true],
                'vitaminas_minerales' => ['label' => 'Vitaminas, minerales o reconstituyente', 'category' => 'suplementacion', 'subtype' => 'vitaminas', 'plan' => true],
                'tratamiento_indicado' => ['label' => 'Tratamiento indicado por veterinario', 'category' => 'enfermedad', 'subtype' => 'otra', 'plan' => true],
            ],
        ],
        'procedimiento' => [
            'label' => 'Procedimiento o manejo',
            'items' => [
                'curacion' => ['label' => 'Curación o cambio de vendaje', 'category' => 'procedimiento', 'subtype' => 'curacion', 'location' => true],
                'podologia' => ['label' => 'Recorte o atención de pezuñas', 'category' => 'procedimiento', 'subtype' => 'podologia', 'location' => true],
                'castracion' => ['label' => 'Castración', 'category' => 'procedimiento', 'subtype' => 'castracion'],
                'descornado' => ['label' => 'Descornado', 'category' => 'procedimiento', 'subtype' => 'descornado'],
                'cirugia' => ['label' => 'Cirugía o procedimiento asistido', 'category' => 'procedimiento', 'subtype' => 'cirugia'],
                'muestra_prueba' => ['label' => 'Toma de muestra o prueba diagnóstica', 'category' => 'procedimiento', 'subtype' => 'diagnostico'],
                'otro_procedimiento' => ['label' => 'Otro procedimiento', 'category' => 'procedimiento', 'subtype' => 'otro'],
            ],
        ],
        'control' => [
            'label' => 'Revisión o seguimiento',
            'items' => [
                'revision_general' => ['label' => 'Revisión general', 'category' => 'control', 'subtype' => 'revision'],
                'evolucion' => ['label' => 'Control de evolución', 'category' => 'control', 'subtype' => 'seguimiento'],
                'revision_cuarentena' => ['label' => 'Revisión de cuarentena', 'category' => 'control', 'subtype' => 'cuarentena'],
                'revision_posparto' => ['label' => 'Revisión posparto', 'category' => 'control', 'subtype' => 'posparto'],
                'revision_compra_venta' => ['label' => 'Revisión para compra o venta', 'category' => 'control', 'subtype' => 'comercial'],
            ],
        ],
    ];

    private const VIAS = [
        'oral' => 'Oral',
        'subcutanea' => 'Subcutánea',
        'intramuscular' => 'Intramuscular',
        'intravenosa' => 'Intravenosa',
        'topica' => 'Tópica / baño',
        'intramamaria' => 'Intramamaria',
        'ocular' => 'Ocular',
        'otra' => 'Otra vía',
    ];

    #[Locked]
    public $sanId = null;

    #[Locked]
    public bool $isEdit = false;

    public array $animalIds = [];

    public string $fechaEvento = '';

    public string $motivoAtencion = '';

    public bool $mostrarSelectorMotivo = true;

    public string $nivelAtencion = 'estable';

    public bool $requiereAislamiento = false;

    public string $ubicacionCorporal = '';

    public string $sintomasDiagnostico = '';

    public bool $administraProducto = false;

    public string $productoOpcion = '';

    public string $productoMarca = '';

    public string $dosisCantidad = '';

    public string $viaAdministracion = '';

    public int|string $numeroAplicaciones = 1;

    public int|string $intervaloDias = 1;

    public int|string $retiroCarneDias = 0;

    public int|string $retiroLecheHoras = 0;

    public string $responsable = '';

    public array $animales = [];

    public array $medicamentos = [];

    public array $dosisPlan = [];

    // Campos técnicos derivados; se conservan públicos para edición y pruebas.
    public string $categoriaSalud = '';

    public string $subtipo = '';

    public string $severidad = '';

    public string $estadoSeguimiento = 'en_seguimiento';

    public bool $usaPlanDosis = false;

    public function mount($id = null): void
    {
        $this->fechaEvento = now()->toDateString();
        $this->responsable = (string) (auth()->user()?->name ?? '');
        $this->loadMedicamentos();

        if ($id) {
            $record = SanidadRegistro::with('dosisPlan')
                ->where('fundo_id', session('fundo_id'))
                ->findOrFail($id);

            $this->sanId = $record->id;
            $this->isEdit = true;
            $this->animalIds = [(string) $record->animal_id];
            $this->fechaEvento = $record->fecha_evento->format('Y-m-d');
            $this->categoriaSalud = $record->categoria_salud ?: 'otro';
            $this->subtipo = $record->subtipo ?: 'otro';
            $this->motivoAtencion = $this->inferMotive($this->categoriaSalud, $this->subtipo);
            $this->nivelAtencion = match ($record->severidad) {
                'alta' => 'urgente',
                'moderada' => 'vigilar',
                default => 'estable',
            };
            $this->requiereAislamiento = $record->estado_seguimiento === 'cuarentena';
            $this->ubicacionCorporal = $record->ubicacion_corporal ?: '';
            $this->sintomasDiagnostico = $record->sintomas_diagnostico ?: '';
            $this->responsable = $record->responsable ?: $this->responsable;
            $this->estadoSeguimiento = $record->estado_seguimiento ?: 'en_seguimiento';
            $this->retiroCarneDias = $record->retiro_carne_dias ?? 0;
            $this->retiroLecheHoras = $record->retiro_leche_horas ?? 0;
            $this->dosisPlan = $record->dosisPlan->map(fn (TratamientoDosis $dose) => [
                'fecha_programada' => $dose->fecha_programada->format('Y-m-d'),
                'aplicada' => (bool) $dose->aplicada,
                'fecha_aplicada' => $dose->fecha_aplicada?->format('Y-m-d') ?? '',
            ])->values()->all();
            $this->administraProducto = $record->dosisPlan->isNotEmpty() || filled($record->producto_marca);
            $this->usaPlanDosis = $this->administraProducto;
            $this->numeroAplicaciones = max(1, $record->dosisPlan->count());
            $this->intervaloDias = $this->guessInterval($record->dosisPlan);

            $firstDose = $record->dosisPlan->first();
            if ($firstDose?->medicamento_id) {
                $this->productoOpcion = 'med:'.$firstDose->medicamento_id;
            } elseif ($this->administraProducto) {
                $this->productoOpcion = 'otro';
            }
            $this->productoMarca = $record->producto_marca ?: ($firstDose?->medicamento_nombre ?? '');
            $this->dosisCantidad = $firstDose?->dosis ?? '';
            $this->viaAdministracion = $this->normalizeRoute($firstDose?->via);
            $this->loadRecordPhotos($record);
            $this->mostrarSelectorMotivo = false;
        }

        if (! $this->isEdit) {
            $this->prefillMedicineFromRequest();
        }

        $this->loadAnimales();
    }

    public function loadAnimales(): void
    {
        $fundoId = (int) session('fundo_id');
        $selectedIds = array_map('intval', $this->animalIds);

        $this->animales = Animal::query()
            ->where('fundo_id', $fundoId)
            ->where(function ($query) use ($selectedIds) {
                $query->where('activo', true);
                if ($selectedIds !== []) {
                    $query->orWhereIn('id', $selectedIds);
                }
            })
            ->with(['especie:id,nombre', 'raza:id,nombre'])
            ->orderBy('arete')
            ->get([
                'id', 'arete', 'nombre', 'especie_id', 'raza_id', 'genero', 'foto_ruta', 'foto_encuadre',
                'fecha_nacimiento', 'fecha_alta', 'edad_estimada_meses_alta',
            ])
            ->map(fn (Animal $animal) => [
                'id' => (string) $animal->id,
                'code' => $animal->arete,
                'name' => $animal->nombre ?: 'Sin nombre',
                'type' => $animal->clasificacion_edad,
                'species' => $animal->especie?->nombre ?? 'Sin especie',
                'breed' => $animal->raza?->nombre ?? 'Sin raza',
                'sex' => $animal->genero === 'hembra' ? 'Hembra' : 'Macho',
                'photo' => $animal->foto_ruta ? url('/storage/'.ltrim($animal->foto_ruta, '/')) : null,
                'frame' => ImageFrame::normalize($animal->foto_encuadre),
            ])->values()->all();
    }

    public function loadMedicamentos(): void
    {
        $fundoId = (int) session('fundo_id');
        $this->medicamentos = Medicamento::query()
            ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->where('activo', true)
            ->orderBy('nombre')
            ->withSum(['lotes as stock_total' => fn ($query) => $query
                ->where('fundo_id', $fundoId)->where('activo', true)
                ->whereDate('fecha_vencimiento', '>=', today())], 'cantidad_disponible')
            ->withCount(['lotes as lotes_count' => fn ($query) => $query->where('fundo_id', $fundoId)])
            ->get(['id', 'nombre', 'presentacion', 'via_predeterminada', 'unidad_stock'])
            ->map(fn (Medicamento $medicine) => [
                'id' => (string) $medicine->id,
                'nombre' => $medicine->nombre.($medicine->presentacion ? ' ('.$medicine->presentacion.')' : ''),
                'via' => $medicine->via_predeterminada,
                'unidad' => $medicine->unidad_stock ?: 'unidad',
                'stock' => round((float) ($medicine->stock_total ?? 0), 3),
                'controla_stock' => (int) $medicine->lotes_count > 0,
            ])->all();
    }

    public function updatedProductoOpcion(): void
    {
        $medicine = $this->selectedMedicineData();
        if ($medicine && ! $this->viaAdministracion && $medicine['via']) {
            $this->viaAdministracion = $medicine['via'];
        }
    }

    public function updatedMotivoAtencion(): void
    {
        $motive = $this->selectedMotive();
        if (! $motive) {
            $this->mostrarSelectorMotivo = true;
            $this->categoriaSalud = '';
            $this->subtipo = '';
            $this->administraProducto = false;
            $this->dosisPlan = [];

            return;
        }

        $this->categoriaSalud = $motive['category'];
        $this->subtipo = $motive['subtype'];
        $this->mostrarSelectorMotivo = false;
        $this->ubicacionCorporal = ($motive['location'] ?? false) ? $this->ubicacionCorporal : '';
        $this->administraProducto = (bool) ($motive['plan'] ?? false);
        $this->usaPlanDosis = $this->administraProducto;
        if ($this->administraProducto) {
            $this->numeroAplicaciones = 1;
            $this->retiroCarneDias = 0;
            $this->retiroLecheHoras = 0;
            $this->rebuildDosePlan(false);
        } else {
            $this->dosisPlan = [];
            $this->productoOpcion = '';
            $this->productoMarca = '';
            $this->dosisCantidad = '';
            $this->viaAdministracion = '';
        }
    }

    public function selectMotive(string $motive): void
    {
        if (! in_array($motive, $this->motiveKeys(), true)) {
            return;
        }

        $this->motivoAtencion = $motive;
        $this->updatedMotivoAtencion();
    }

    public function showMotivePicker(): void
    {
        $this->mostrarSelectorMotivo = true;
    }

    public function updatedAdministraProducto(): void
    {
        $this->usaPlanDosis = $this->administraProducto;
        if ($this->administraProducto) {
            $this->numeroAplicaciones = max(1, (int) $this->numeroAplicaciones);
            $this->rebuildDosePlan(true);
        } else {
            $this->dosisPlan = [];
        }
    }

    public function updatedNumeroAplicaciones(): void
    {
        $this->numeroAplicaciones = min(6, max(1, (int) $this->numeroAplicaciones));
        $this->rebuildDosePlan(true);
    }

    public function updatedIntervaloDias(): void
    {
        $this->intervaloDias = min(365, max(0, (int) $this->intervaloDias));
        $this->rebuildDosePlan(false);
    }

    public function updatedFechaEvento(): void
    {
        if ($this->administraProducto) {
            $this->rebuildDosePlan(false);
        }
    }

    public function updatedDosisPlan(): void
    {
        foreach ($this->dosisPlan as &$dose) {
            if ((bool) ($dose['aplicada'] ?? false)) {
                $dose['fecha_aplicada'] = ($dose['fecha_aplicada'] ?? '') ?: now()->toDateString();
            } else {
                $dose['fecha_aplicada'] = '';
            }
        }
        unset($dose);
    }

    public function save()
    {
        $this->authorizePermission('monitoreo', $this->isEdit ? 'actualizar' : 'crear');

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $motive = $this->selectedMotive();
        if ($motive) {
            $this->categoriaSalud = $motive['category'];
            $this->subtipo = $motive['subtype'];
        }
        $this->sintomasDiagnostico = $this->normalizeText($this->sintomasDiagnostico) ?? '';
        $this->productoMarca = $this->normalizeText($this->productoMarca) ?? '';
        $this->dosisCantidad = $this->normalizeText($this->dosisCantidad) ?? '';
        $this->ubicacionCorporal = $this->normalizeText($this->ubicacionCorporal) ?? '';
        $this->responsable = $this->normalizeText($this->responsable) ?? '';

        $this->validate($this->rules($fundoId), $this->validationMessages());

        $plan = $this->normalizedPlan();
        $this->estadoSeguimiento = $this->deriveState($plan);
        $this->severidad = $this->currentGroupKey() === 'problema'
            ? match ($this->nivelAtencion) {
                'urgente' => 'alta',
                'vigilar' => 'moderada',
                default => 'leve',
            }
        : '';

        $pendingDose = collect($plan)->where('aplicada', false)->sortBy('fecha_programada')->first();
        $motiveLabel = $motive['label'] ?? 'Evento de salud';
        $productLabel = $this->selectedProductLabel();
        $attentionSummary = $this->administraProducto
            ? collect([$productLabel, $this->dosisCantidad, self::VIAS[$this->viaAdministracion] ?? null])->filter()->join(' · ')
            : ($this->currentGroupKey() === 'procedimiento' ? $motiveLabel : null);
        $legacyType = in_array($this->categoriaSalud, ['parasitos', 'vacunacion', 'suplementacion'], true) ? 'preventivo' : 'clinico';
        $legacyIntervention = match ($this->categoriaSalud) {
            'vacunacion' => 'vacuna',
            'parasitos' => $this->subtipo === 'externos' ? 'desparasitante_externo' : 'desparasitante_interno',
            'suplementacion' => 'vitamina',
            default => null,
        };
        $legacyClassification = match ($this->categoriaSalud) {
            'lesion', 'procedimiento' => 'lesion_accidente',
            'enfermedad' => $this->subtipo === 'metabolica' ? 'trastorno_metabolico' : 'enfermedad_infecciosa',
            default => 'enfermedad_infecciosa',
        };
        $legacyState = match ($this->estadoSeguimiento) {
            'completado' => 'recuperada',
            'critico' => 'critico',
            'cuarentena' => 'cuarentena',
            default => 'en_tratamiento',
        };
        $isBatch = count($this->animalIds) > 1;
        $data = [
            'fundo_id' => $fundoId,
            'categoria_salud' => $this->categoriaSalud,
            'subtipo' => $this->subtipo,
            'severidad' => $this->severidad ?: null,
            'ubicacion_corporal' => $this->ubicacionCorporal ?: null,
            'estado_seguimiento' => $this->estadoSeguimiento,
            'tipo_evento' => $legacyType,
            'alcance' => $isBatch ? 'lote' : 'individual',
            'tipo_intervencion' => $legacyIntervention,
            'producto_marca' => $this->administraProducto ? ($productLabel ?: null) : null,
            'proposito' => self::MOTIVOS[$this->currentGroupKey()]['label'] ?? null,
            'responsable' => $this->responsable ?: null,
            'retiro_carne_dias' => $this->administraProducto ? (int) $this->retiroCarneDias : null,
            'retiro_leche_horas' => $this->administraProducto ? (int) $this->retiroLecheHoras : null,
            'proxima_dosis' => $pendingDose['fecha_programada'] ?? null,
            'fecha_evento' => $this->fechaEvento,
            'clasificacion' => $legacyClassification,
            'sintomas_diagnostico' => $this->sintomasDiagnostico ?: $motiveLabel,
            'tratamiento' => $attentionSummary,
            'estado_clinico' => $legacyState,
            'fecha_cierre' => $this->estadoSeguimiento === 'completado' ? $this->fechaEvento : null,
            'observaciones_cierre' => null,
            'medicamento_id' => null,
            'medicamento_nombre' => null,
            'dosis_via' => null,
        ];

        $record = $this->sanId
            ? SanidadRegistro::with('dosisPlan')->where('fundo_id', $fundoId)->findOrFail($this->sanId)
            : new SanidadRegistro;
        $newPhotoPaths = [];

        try {
            $newPhotoPaths = $this->storeRecordPhotos('monitoreo/sanidad');

            [$savedRecords, $removedPaths] = DB::transaction(function () use ($data, $fundoId, $record, $newPhotoPaths, $plan) {
                $saved = collect();
                $removedPaths = $this->isEdit ? $this->removeMarkedRecordPhotos($record) : [];
                $animals = Animal::query()->where('fundo_id', $fundoId)->whereIn('id', $this->animalIds)->get()->keyBy(fn (Animal $animal) => (string) $animal->id);
                $inventory = app(MedicamentoInventoryService::class);

                foreach ($this->animalIds as $index => $animalId) {
                    $healthEvent = $this->isEdit && $index === 0 ? $record : new SanidadRegistro;
                    $healthEvent->fill([...$data, 'animal_id' => (int) $animalId])->save();
                    $this->attachRecordPhotos($healthEvent, $newPhotoPaths);

                    if ($this->isEdit && $index === 0) {
                        $inventory->revertDoses($healthEvent->dosisPlan);
                        $healthEvent->dosisPlan()->delete();
                    }

                    foreach ($plan as $doseData) {
                        $dose = $healthEvent->dosisPlan()->create([...$doseData, 'fundo_id' => $fundoId]);
                        if ($dose->aplicada) {
                            $inventory->consumeDose($dose->load('eventoSalud'));
                        } else {
                            AlertaProgramada::create([
                                'fundo_id' => $fundoId,
                                'animal_id' => (int) $animalId,
                                'tratamiento_dosis_id' => $dose->id,
                                'tipo' => 'proxima_dosis',
                                'fecha_alerta' => $dose->fecha_programada,
                                'mensaje' => 'Aplicar D'.$dose->numero.' de '.($dose->medicamento_nombre ?: $this->selectedProductLabel() ?: 'producto').' a '.($animals->get((string) $animalId)?->arete ?? 'animal'),
                            ]);
                        }
                    }

                    if ($this->estadoSeguimiento === 'cuarentena' && ! $this->isEdit) {
                        AlertaProgramada::create([
                            'fundo_id' => $fundoId,
                            'animal_id' => (int) $animalId,
                            'tipo' => 'cuarentena',
                            'fecha_alerta' => now()->addDays(7)->toDateString(),
                            'mensaje' => 'Revisar aislamiento de '.($animals->get((string) $animalId)?->arete ?? 'animal').' en 7 días.',
                        ]);
                    }

                    $saved->push($healthEvent);
                }

                return [$saved, $removedPaths];
            });
        } catch (Throwable $exception) {
            $this->deleteStoredRecordPhotos($newPhotoPaths);

            throw $exception;
        }

        $this->deleteUnreferencedRecordPhotos($removedPaths);
        $count = $savedRecords->count();
        session()->flash('swal', [
            'icon' => 'success',
            'title' => $this->isEdit ? '¡Actualizado!' : '¡Registrado!',
            'text' => $this->isEdit
                ? 'Evento de salud actualizado.'
                : ($count === 1 ? 'Evento de salud registrado.' : "Evento registrado para {$count} animales."),
        ]);
        if ($savedRecords->isNotEmpty()) {
            $this->publishRecentRecord('monitoreo.sanidad', $savedRecords->first());
        }

        return $this->redirectRoute('monitoreo.index', navigate: true);
    }

    private function rules(int $fundoId): array
    {
        return [
            'motivoAtencion' => ['required', Rule::in($this->motiveKeys())],
            'animalIds' => $this->isEdit ? ['required', 'array', 'size:1'] : ['required', 'array', 'min:1'],
            'animalIds.*' => ['required', 'distinct', Rule::exists('animales', 'id')->where(fn ($query) => $query->where('fundo_id', $fundoId))],
            'fechaEvento' => ['required', 'date', 'before_or_equal:today'],
            'nivelAtencion' => [Rule::requiredIf($this->currentGroupKey() === 'problema'), Rule::in(['estable', 'vigilar', 'urgente'])],
            'requiereAislamiento' => ['boolean'],
            'ubicacionCorporal' => ['nullable', 'string', 'max:150'],
            'sintomasDiagnostico' => ['nullable', 'string', 'max:3000'],
            'administraProducto' => ['boolean'],
            'productoOpcion' => [Rule::requiredIf($this->administraProducto), 'nullable', Rule::in(array_keys($this->productOptions()))],
            'productoMarca' => [Rule::requiredIf($this->administraProducto && $this->productoOpcion === 'otro'), 'nullable', 'string', 'max:255'],
            'dosisCantidad' => [
                Rule::requiredIf($this->administraProducto), 'nullable', 'string', 'max:100',
                ...($this->selectedMedicineData()['controla_stock'] ?? false ? ['regex:/^\s*\d+(?:[\.,]\d{1,3})?/u'] : []),
            ],
            'viaAdministracion' => [Rule::requiredIf($this->administraProducto), 'nullable', Rule::in(array_keys(self::VIAS))],
            'numeroAplicaciones' => [Rule::requiredIf($this->administraProducto), 'integer', 'min:1', 'max:6'],
            'intervaloDias' => [Rule::requiredIf($this->administraProducto && (int) $this->numeroAplicaciones > 1), 'integer', 'min:0', 'max:365'],
            'retiroCarneDias' => [Rule::requiredIf($this->administraProducto), 'integer', 'min:0', 'max:3650'],
            'retiroLecheHoras' => [Rule::requiredIf($this->administraProducto), 'integer', 'min:0', 'max:8760'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'dosisPlan' => [$this->administraProducto ? 'required' : 'nullable', 'array', $this->administraProducto ? 'size:'.(int) $this->numeroAplicaciones : 'max:0'],
            'dosisPlan.*.fecha_programada' => ['required', 'date'],
            'dosisPlan.*.aplicada' => ['boolean'],
            'dosisPlan.*.fecha_aplicada' => ['nullable', 'date', 'before_or_equal:today'],
            ...$this->recordPhotoRules(),
        ];
    }

    private function normalizedPlan(): array
    {
        if (! $this->administraProducto) {
            return [];
        }

        $medicineId = str_starts_with($this->productoOpcion, 'med:')
            ? (int) substr($this->productoOpcion, 4)
            : null;
        $medicineName = $medicineId === null ? $this->productoMarca : null;
        $medicine = $this->selectedMedicineData();
        $inventoryQuantity = ($medicine['controla_stock'] ?? false)
            ? app(MedicamentoInventoryService::class)->quantityFromDose($this->dosisCantidad)
            : null;

        return collect($this->dosisPlan)->values()->map(function (array $dose, int $index) use ($medicineId, $medicineName, $medicine, $inventoryQuantity) {
            $applied = (bool) ($dose['aplicada'] ?? false);

            return [
                'numero' => $index + 1,
                'medicamento_id' => $medicineId,
                'medicamento_nombre' => $medicineName ?: null,
                'dosis' => $this->dosisCantidad,
                'cantidad_inventario' => $inventoryQuantity,
                'unidad_inventario' => $inventoryQuantity ? ($medicine['unidad'] ?? 'unidad') : null,
                'via' => self::VIAS[$this->viaAdministracion] ?? $this->viaAdministracion,
                'fecha_programada' => $dose['fecha_programada'],
                'aplicada' => $applied,
                'fecha_aplicada' => $applied ? (($dose['fecha_aplicada'] ?? '') ?: now()->toDateString()) : null,
                'responsable' => $this->responsable ?: null,
            ];
        })->all();
    }

    private function rebuildDosePlan(bool $preserveDates): void
    {
        if (! $this->administraProducto || ! $this->fechaEvento) {
            $this->dosisPlan = [];

            return;
        }

        $count = min(6, max(1, (int) $this->numeroAplicaciones));
        $interval = min(365, max(0, (int) $this->intervaloDias));
        $start = CarbonImmutable::parse($this->fechaEvento);
        $existing = $this->dosisPlan;
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $old = $existing[$index] ?? [];
            $rows[] = [
                'fecha_programada' => $preserveDates && filled($old['fecha_programada'] ?? null)
                    ? $old['fecha_programada']
                    : $start->addDays($index * $interval)->toDateString(),
                'aplicada' => array_key_exists('aplicada', $old) ? (bool) $old['aplicada'] : $index === 0,
                'fecha_aplicada' => $old['fecha_aplicada'] ?? ($index === 0 ? now()->toDateString() : ''),
            ];
        }

        $this->dosisPlan = $rows;
    }

    private function deriveState(array $plan): string
    {
        if ($this->requiereAislamiento) {
            return 'cuarentena';
        }
        if ($this->currentGroupKey() === 'problema' && $this->nivelAtencion === 'urgente') {
            return 'critico';
        }
        if (collect($plan)->contains('aplicada', false)) {
            return 'en_seguimiento';
        }
        if ($this->currentGroupKey() === 'problema') {
            return $this->isEdit && $this->estadoSeguimiento === 'completado' ? 'completado' : 'en_seguimiento';
        }

        return 'completado';
    }

    private function selectedMotive(): ?array
    {
        foreach (self::MOTIVOS as $group) {
            if (isset($group['items'][$this->motivoAtencion])) {
                return $group['items'][$this->motivoAtencion];
            }
        }

        return null;
    }

    private function selectedMedicineData(): ?array
    {
        if (! str_starts_with($this->productoOpcion, 'med:')) {
            return null;
        }

        $id = substr($this->productoOpcion, 4);

        return collect($this->medicamentos)->firstWhere('id', (string) $id);
    }

    private function prefillMedicineFromRequest(): void
    {
        $medicineId = request()->integer('medicamento');
        if (! $medicineId || ! collect($this->medicamentos)->contains('id', (string) $medicineId)) {
            return;
        }

        $this->selectMotive('tratamiento_indicado');
        $this->productoOpcion = 'med:'.$medicineId;
        $this->updatedProductoOpcion();
    }

    private function currentGroupKey(): string
    {
        foreach (self::MOTIVOS as $key => $group) {
            if (isset($group['items'][$this->motivoAtencion])) {
                return $key;
            }
        }

        return '';
    }

    private function motiveKeys(): array
    {
        return collect(self::MOTIVOS)->flatMap(fn (array $group) => array_keys($group['items']))->values()->all();
    }

    private function inferMotive(string $category, string $subtype): string
    {
        return match ($category) {
            'vacunacion' => 'vacunacion',
            'parasitos' => $subtype === 'externos' ? 'parasitos_externos' : 'parasitos_internos',
            'suplementacion' => 'vitaminas_minerales',
            'lesion' => $subtype === 'cojera' ? 'locomotor' : 'herida_piel',
            'enfermedad' => match ($subtype) {
                'respiratoria' => 'respiratorio',
                'digestiva' => 'digestivo',
                'reproductiva' => 'reproductivo',
                'mamaria' => 'ubre_leche',
                'ocular' => 'ocular',
                'metabolica' => 'metabolico',
                default => 'signos_generales',
            },
            'procedimiento' => match ($subtype) {
                'curacion' => 'curacion',
                'podologia' => 'podologia',
                'castracion' => 'castracion',
                'descornado' => 'descornado',
                'cirugia' => 'cirugia',
                'diagnostico' => 'muestra_prueba',
                default => 'otro_procedimiento',
            },
            'control' => match ($subtype) {
                'seguimiento' => 'evolucion',
                'cuarentena' => 'revision_cuarentena',
                'posparto' => 'revision_posparto',
                'comercial' => 'revision_compra_venta',
                default => 'revision_general',
            },
            default => 'otro_signo',
        };
    }

    private function productOptions(): array
    {
        $options = ['' => 'Selecciona el producto'];
        foreach ($this->medicamentos as $medicine) {
            $options['med:'.$medicine['id']] = $medicine['nombre'];
        }
        $options['otro'] = 'Otro producto / no está en catálogo';

        return $options;
    }

    private function selectedProductLabel(): string
    {
        if ($this->productoOpcion === 'otro') {
            return $this->productoMarca;
        }

        return $this->productOptions()[$this->productoOpcion] ?? $this->productoMarca;
    }

    private function guessInterval($doses): int
    {
        if ($doses->count() < 2) {
            return 1;
        }

        return max(0, $doses[0]->fecha_programada->diffInDays($doses[1]->fecha_programada));
    }

    private function normalizeRoute(?string $route): string
    {
        $normalized = mb_strtolower(trim((string) $route), 'UTF-8');
        foreach (self::VIAS as $key => $label) {
            if ($normalized === mb_strtolower($label, 'UTF-8')) {
                return $key;
            }
        }

        return $normalized !== '' ? 'otra' : '';
    }

    private function normalizeText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? mb_strtolower($value, 'UTF-8') : null;
    }

    private function validationMessages(): array
    {
        return array_merge([
            'motivoAtencion.required' => 'Selecciona el motivo del registro.',
            'animalIds.required' => 'Selecciona al menos un animal.',
            'animalIds.min' => 'Selecciona al menos un animal.',
            'animalIds.size' => 'En edición solo puede mantenerse un animal.',
            'animalIds.*.exists' => 'La selección contiene un animal no válido.',
            'fechaEvento.before_or_equal' => 'La fecha no puede ser futura.',
            'productoOpcion.required' => 'Selecciona el producto aplicado.',
            'productoMarca.required' => 'Escribe el nombre del producto.',
            'dosisCantidad.required' => 'Indica la dosis total administrada.',
            'dosisCantidad.regex' => 'Empieza la dosis con una cantidad, por ejemplo: 5 ml.',
            'viaAdministracion.required' => 'Selecciona la vía de administración.',
            'dosisPlan.size' => 'El calendario no coincide con el número de aplicaciones.',
            'dosisPlan.*.fecha_programada.required' => 'Indica la fecha de cada aplicación.',
        ], $this->recordPhotoMessages());
    }

    public function render()
    {
        $motive = $this->selectedMotive();

        return view('livewire.monitoreo.sanidad-form', [
            'motivoGroups' => self::MOTIVOS,
            'motivoActual' => $motive,
            'grupoActual' => $this->currentGroupKey(),
            'productoOptions' => $this->productOptions(),
            'viaOptions' => self::VIAS,
            'productoLabel' => $this->selectedProductLabel(),
            'inventarioSeleccionado' => $this->selectedMedicineData(),
        ])->layout('layouts.app');
    }
}
