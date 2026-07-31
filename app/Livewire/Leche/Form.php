<?php

namespace App\Livewire\Leche;

use App\Models\Animal;
use App\Models\Ordeno;
use App\Models\OrdenoFotoDiaria;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Traits\PublishesRecentRecord;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Form extends Component
{
    use AuthorizesPermissions, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $ordenoId = null;

    #[Locked]
    public $isEdit = false;

    public $fecha = '';

    public $turno = 'manana';

    public $tipoRegistro = 'individual';

    public $litrosTotal = '';

    public $cantidadVacas = '';

    public $vacas = [];

    public $detalles = [];

    public $observaciones = '';

    public $foto = null;

    public array $fotoEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $fotoEncuadreChanged = false;

    public $existingFoto = null;

    public $removeFoto = false;

    public $photoConfirmed = false;

    protected $listeners = [
        'confirmarCambioFoto' => 'confirmPhotoChange',
        'cancelarCambioFoto' => 'cancelPhotoChange',
        'confirmarEliminacionFoto' => 'confirmPhotoRemoval',
    ];

    public function mount($id = null)
    {
        $this->authorizePermission('leche', $id !== null ? 'actualizar' : 'crear');

        $fundoId = session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $this->fecha = now()->toDateString();

        if ($id !== null) {
            $ordeno = Ordeno::with('detalles')
                ->where('fundo_id', $fundoId)
                ->findOrFail($id);

            $this->ordenoId = $ordeno->id;
            $this->isEdit = true;
            $this->fecha = $ordeno->fecha->toDateString();
            $this->turno = $ordeno->turno;
            $this->tipoRegistro = $ordeno->tipo_registro;
            $this->litrosTotal = $ordeno->tipo_registro === 'lote' ? $ordeno->litros_total : '';
            $this->cantidadVacas = $ordeno->tipo_registro === 'lote' ? $ordeno->cantidad_vacas : '';
            $this->observaciones = $ordeno->observaciones ?? '';

            $detalles = $ordeno->detalles->mapWithKeys(fn ($detalle) => [
                $detalle->animal_id => [
                    'litros' => $detalle->causa_excepcion ? '' : $detalle->litros,
                    'causa_excepcion' => $detalle->causa_excepcion ?? '',
                    'justificacion_otros' => $detalle->justificacion_otros ?? '',
                ],
            ])->all();

            $this->loadVacas($detalles);
        } else {
            $this->loadVacas();
        }

        $this->loadDailyPhoto();
    }

    public function loadVacas(?array $currentDetails = null)
    {
        $currentDetails ??= is_array($this->detalles) ? $this->detalles : [];
        $this->vacas = $this->queryAllowedVacas();
        $detalles = [];

        foreach ($this->vacas as $vaca) {
            $vacaId = (int) data_get($vaca, 'id');
            $detalle = data_get($currentDetails, (string) $vacaId, []);

            $detalles[$vacaId] = [
                'litros' => data_get($detalle, 'litros', ''),
                'causa_excepcion' => data_get($detalle, 'causa_excepcion', ''),
                'justificacion_otros' => data_get($detalle, 'justificacion_otros', ''),
            ];
        }

        $this->detalles = $detalles;
    }

    public function updatedTipoRegistro()
    {
        $this->resetValidation();

        if ($this->tipoRegistro === 'individual') {
            $this->loadVacas();
        }
    }

    public function updatedFecha()
    {
        $this->reset('foto');
        $this->photoConfirmed = false;
        $this->loadDailyPhoto();
    }

    public function updatedFoto()
    {
        $this->removeFoto = false;
        $this->photoConfirmed = false;
        $this->validateOnly('foto', [
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ], [
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.mimes' => 'Usa una imagen JPG, PNG o WebP.',
            'foto.max' => 'La imagen optimizada no puede superar 2 MB.',
            'foto.dimensions' => 'La imagen supera dimensiones permitidas.',
        ]);

        if (! $this->foto) {
            return;
        }

        $this->fotoEncuadre = ImageFrame::DEFAULT;
        $this->photoConfirmed = true;
    }

    public function updatedFotoEncuadre(): void
    {
        $this->fotoEncuadreChanged = true;
    }

    public function confirmPhotoChange(): void
    {
        if (! $this->foto) {
            return;
        }

        $this->photoConfirmed = true;
        $this->removeFoto = false;
        $this->resetValidation('foto');

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Imagen preparada',
            'text' => 'Se aplicará cuando guardes el ordeño.',
        ]);
    }

    public function cancelPhotoChange(): void
    {
        $this->reset('foto');
        $this->photoConfirmed = false;
        $this->resetValidation('foto');
        $this->loadDailyPhoto();
    }

    public function requestPhotoRemoval(): void
    {
        if (! $this->existingFoto) {
            $this->cancelPhotoChange();

            return;
        }

        $this->dispatch('swal:confirm', [
            'title' => '¿Preparar eliminación?',
            'text' => 'La foto seguirá protegida hasta que guardes el ordeño. También podrás deshacer esta acción.',
            'icon' => 'warning',
            'confirmButtonText' => 'Preparar eliminación',
            'cancelButtonText' => 'Mantener foto',
            'event' => 'confirmarEliminacionFoto',
        ]);
    }

    public function confirmPhotoRemoval(): void
    {
        $this->reset('foto');
        $this->photoConfirmed = false;
        $this->removeFoto = true;
        $this->resetValidation('foto');

        $this->dispatch('swal:toast', [
            'icon' => 'info',
            'title' => 'Eliminación preparada',
            'text' => 'La foto actual sigue protegida hasta guardar.',
        ]);
    }

    public function cancelPhotoRemoval(): void
    {
        $this->removeFoto = false;
        $this->loadDailyPhoto();
    }

    public function save()
    {
        $this->authorizePermission('leche', $this->isEdit ? 'actualizar' : 'crear');

        $fundoId = session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $rules = [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'turno' => ['required', Rule::in(['manana', 'tarde', 'noche'])],
            'tipoRegistro' => ['required', Rule::in(['individual', 'lote'])],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
            'removeFoto' => ['boolean'],
            'photoConfirmed' => ['boolean'],
            ...ImageFrame::rules('fotoEncuadre'),
        ];

        if ($this->tipoRegistro === 'lote') {
            $rules['litrosTotal'] = ['required', 'numeric', 'min:0'];
            $rules['cantidadVacas'] = ['required', 'integer', 'min:1'];
        } else {
            $rules += [
                'detalles' => ['required', 'array', 'min:1'],
                'detalles.*' => ['required', 'array'],
                'detalles.*.causa_excepcion' => ['nullable', Rule::in([
                    'secado',
                    'mastitis',
                    'enfermedad',
                    'dosificacion',
                    'cria_reciente',
                    'baja_produccion',
                    'otros',
                ])],
            ];
        }

        $messages = [
            'fecha.before_or_equal' => 'La fecha del ordeño no puede ser futura.',
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.max' => 'La imagen no puede superar los 5 MB.',
            'detalles.required' => 'Debe existir al menos una vaca para el registro individual.',
            'detalles.min' => 'Debe existir al menos una vaca para el registro individual.',
            'detalles.*.causa_excepcion.in' => 'La causa de excepción seleccionada no es válida.',
        ];

        $this->validate($rules, $messages);

        if ($this->foto && ! $this->photoConfirmed) {
            throw ValidationException::withMessages([
                'foto' => 'Confirma la nueva imagen antes de guardar.',
            ]);
        }

        $itemsParaGuardar = [];
        $totalLitros = (float) $this->litrosTotal;
        $ordenadasCount = (int) $this->cantidadVacas;

        if ($this->tipoRegistro === 'individual') {
            $allowedAnimalIds = $this->queryAllowedVacas()
                ->pluck('id')
                ->map(fn ($animalId) => (int) $animalId)
                ->sort()
                ->values()
                ->all();
            $submittedAnimalIds = collect(array_keys($this->detalles))
                ->map(fn ($animalId) => filter_var($animalId, FILTER_VALIDATE_INT))
                ->filter(fn ($animalId) => $animalId !== false)
                ->map(fn ($animalId) => (int) $animalId)
                ->sort()
                ->values()
                ->all();

            if ($submittedAnimalIds !== $allowedAnimalIds || count($submittedAnimalIds) !== count($this->detalles)) {
                throw ValidationException::withMessages([
                    'detalles' => 'La lista de vacas cambió o contiene animales no permitidos. Recargue el formulario.',
                ]);
            }

            $conditionalRules = [];
            foreach ($allowedAnimalIds as $animalId) {
                $causa = data_get($this->detalles, $animalId.'.causa_excepcion');
                $conditionalRules['detalles.'.$animalId.'.litros'] = blank($causa)
                    ? ['required', 'numeric', 'min:0']
                    : ['nullable'];
                $conditionalRules['detalles.'.$animalId.'.justificacion_otros'] = $causa === 'otros'
                    ? ['required', 'string', 'max:1000']
                    : ['nullable'];
            }

            $this->validate($conditionalRules, [
                'detalles.*.litros.required' => 'Registre los litros o seleccione una causa de excepción.',
                'detalles.*.litros.numeric' => 'Los litros deben ser un valor numérico.',
                'detalles.*.litros.min' => 'Los litros no pueden ser negativos.',
                'detalles.*.justificacion_otros.required' => 'Debe justificar la causa "Otros".',
                'detalles.*.justificacion_otros.max' => 'La justificación no puede superar los 1000 caracteres.',
            ]);

            $totalLitros = 0;
            $ordenadasCount = 0;

            foreach ($allowedAnimalIds as $animalId) {
                $detalle = data_get($this->detalles, $animalId, []);
                $causa = data_get($detalle, 'causa_excepcion') ?: null;
                $litros = $causa ? 0 : (float) data_get($detalle, 'litros');

                if (! $causa) {
                    $totalLitros += $litros;
                    $ordenadasCount++;
                }

                $itemsParaGuardar[] = [
                    'animal_id' => $animalId,
                    'litros' => $litros,
                    'causa_excepcion' => $causa,
                    'justificacion_otros' => $causa === 'otros'
                        ? trim((string) data_get($detalle, 'justificacion_otros'))
                        : null,
                ];
            }
        }

        $duplicateQuery = Ordeno::where('fundo_id', $fundoId)
            ->whereDate('fecha', $this->fecha)
            ->where('turno', $this->turno);

        if ($this->isEdit) {
            $duplicateQuery->where('id', '!=', $this->ordenoId);
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'turno' => 'Ya existe un registro de ordeño para esta fecha y turno.',
            ]);
        }

        $newPhotoPath = $this->foto
            ? ImageOptimizer::store($this->foto, 'fotos/ordeno', 'foto', 1600, 1536 * 1024, 'public')
            : null;
        $fotoEncuadre = ImageFrame::normalize($this->fotoEncuadre);
        $photosToDelete = [];
        $ordeno = null;

        try {
            DB::transaction(function () use (
                $fundoId,
                $fotoEncuadre,
                $itemsParaGuardar,
                $newPhotoPath,
                &$ordeno,
                &$photosToDelete,
                $ordenadasCount,
                $totalLitros
            ) {
                $duplicateQuery = Ordeno::where('fundo_id', $fundoId)
                    ->whereDate('fecha', $this->fecha)
                    ->where('turno', $this->turno);

                if ($this->isEdit) {
                    $duplicateQuery->where('id', '!=', $this->ordenoId);
                }

                if ($duplicateQuery->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages([
                        'turno' => 'Ya existe un registro de ordeño para esta fecha y turno.',
                    ]);
                }

                $ordeno = $this->isEdit
                    ? Ordeno::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($this->ordenoId)
                    : new Ordeno;
                $originalDate = $ordeno->exists ? $ordeno->fecha->toDateString() : null;

                $ordeno->fill([
                    'fundo_id' => $fundoId,
                    'fecha' => $this->fecha,
                    'turno' => $this->turno,
                    'tipo_registro' => $this->tipoRegistro,
                    'litros_total' => $totalLitros,
                    'cantidad_vacas' => $ordenadasCount,
                    'observaciones' => filled($this->observaciones) ? trim($this->observaciones) : null,
                ])->save();

                $ordeno->detalles()->delete();
                if ($this->tipoRegistro === 'individual') {
                    $ordeno->detalles()->createMany($itemsParaGuardar);
                }

                $dailyPhoto = OrdenoFotoDiaria::where('fundo_id', $fundoId)
                    ->whereDate('fecha', $this->fecha)
                    ->lockForUpdate()
                    ->first();

                if ($newPhotoPath) {
                    $photosToDelete[] = $dailyPhoto?->foto_ruta;

                    if ($dailyPhoto) {
                        $dailyPhoto->update([
                            'foto_ruta' => $newPhotoPath,
                            'foto_encuadre' => $fotoEncuadre,
                        ]);
                    } else {
                        OrdenoFotoDiaria::create([
                            'fundo_id' => $fundoId,
                            'fecha' => $this->fecha,
                            'foto_ruta' => $newPhotoPath,
                            'foto_encuadre' => $fotoEncuadre,
                        ]);
                    }
                } elseif ($this->removeFoto) {
                    $photosToDelete[] = $dailyPhoto?->foto_ruta;
                    $dailyPhoto?->delete();
                } elseif ($dailyPhoto?->foto_ruta && $this->fotoEncuadreChanged) {
                    $dailyPhoto->update(['foto_encuadre' => $fotoEncuadre]);
                }

                if ($originalDate && $originalDate !== $this->fecha) {
                    $originalDateStillUsed = Ordeno::where('fundo_id', $fundoId)
                        ->whereDate('fecha', $originalDate)
                        ->lockForUpdate()
                        ->exists();
                    if (! $originalDateStillUsed) {
                        $oldDailyPhoto = OrdenoFotoDiaria::where('fundo_id', $fundoId)
                            ->whereDate('fecha', $originalDate)
                            ->lockForUpdate()
                            ->first();
                        $photosToDelete[] = $oldDailyPhoto?->foto_ruta;
                        $oldDailyPhoto?->delete();
                    }
                }
            });
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                try {
                    Storage::disk('public')->delete($newPhotoPath);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        foreach (array_unique(array_filter($photosToDelete)) as $photoToDelete) {
            if ($photoToDelete === $newPhotoPath
                || OrdenoFotoDiaria::withoutGlobalScopes()->where('foto_ruta', $photoToDelete)->exists()) {
                continue;
            }

            try {
                Storage::disk('public')->delete($photoToDelete);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => $this->isEdit ? 'Ordeño actualizado' : 'Ordeño registrado',
            'text' => $this->isEdit
                ? 'Los datos del ordeño se actualizaron correctamente.'
                : 'El ordeño se registró correctamente.',
        ]);
        $this->publishRecentRecord('leche.ordenos', $ordeno);

        return redirect()->route('leche.index');
    }

    public function render()
    {
        return view('livewire.leche.form')
            ->layout('layouts.app');
    }

    protected function queryAllowedVacas()
    {
        $fundoId = session('fundo_id');
        $historicalAnimalIds = [];

        if ($this->isEdit && $this->ordenoId) {
            $ordeno = Ordeno::where('fundo_id', $fundoId)->find($this->ordenoId);
            $historicalAnimalIds = $ordeno?->detalles()->pluck('animal_id')->all() ?? [];
        }

        $referenceDate = $this->fecha && strtotime($this->fecha)
            ? CarbonImmutable::parse($this->fecha)
            : CarbonImmutable::today();

        return Animal::withTrashed()
            ->with(['raza:id,nombre', 'especie:id,nombre,codigo_animal'])
            ->where('fundo_id', $fundoId)
            ->where(function ($query) use ($historicalAnimalIds) {
                $query->where(function ($eligible) {
                    $eligible->whereNull('animales.deleted_at')
                        ->whereHas('especie', fn ($especie) => $especie->where('nombre', 'Bovino'))
                        ->where('genero', 'hembra')
                        ->where('apta_ordeno', true)
                        ->where('activo', true);
                });

                if ($historicalAnimalIds !== []) {
                    $query->orWhereIn('animales.id', $historicalAnimalIds);
                }
            })
            ->orderBy('arete')
            ->get([
                'id', 'especie_id', 'raza_id', 'arete', 'nombre', 'genero',
                'fecha_alta', 'fecha_nacimiento', 'edad_estimada_meses_alta',
            ])
            ->filter(fn (Animal $animal) => in_array($animal->id, $historicalAnimalIds, true)
                || $animal->canBeMarkedForMilking($referenceDate))
            ->values();
    }

    protected function loadDailyPhoto(): void
    {
        $this->removeFoto = false;
        $this->photoConfirmed = false;
        $this->fotoEncuadreChanged = false;

        if (! $this->fecha || ! strtotime($this->fecha)) {
            $this->existingFoto = null;
            $this->fotoEncuadre = ImageFrame::DEFAULT;

            return;
        }

        $dailyPhoto = OrdenoFotoDiaria::where('fundo_id', session('fundo_id'))
            ->whereDate('fecha', $this->fecha)
            ->first(['foto_ruta', 'foto_encuadre']);
        $this->existingFoto = $dailyPhoto?->foto_ruta;
        $this->fotoEncuadre = ImageFrame::normalize($dailyPhoto?->foto_encuadre);
    }
}
