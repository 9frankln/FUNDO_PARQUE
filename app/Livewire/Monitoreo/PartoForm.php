<?php

namespace App\Livewire\Monitoreo;

use App\Models\Animal;
use App\Models\Especie;
use App\Models\Parto;
use App\Models\Raza;
use App\Support\AnimalCodeAllocator;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Traits\HandlesRecordPhotos;
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

class PartoForm extends Component
{
    use AuthorizesPermissions, HandlesRecordPhotos, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $partoId = null;

    #[Locked]
    public $isEdit = false;

    public $animalMadreId = '';

    public $fechaParto = '';

    public $tipoParto = 'normal';

    public $criaSexo = 'macho';

    public $criaNombre = '';

    public $criaRazaId = '';

    public $criaPesoNacer = '';

    public $criaEstado = 'vivo_vigoroso';

    public $condicionMadre = 'optima';

    public $observaciones = '';

    public $madres = [];

    public $criaFoto = null;

    public array $criaFotoEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $criaFotoEncuadreChanged = false;

    #[Locked]
    public $existingCriaFoto = null;

    public bool $removeCriaFoto = false;

    public bool $criaPhotoConfirmed = false;

    protected $listeners = [
        'confirmarCambioFotoCria' => 'confirmCriaPhotoChange',
        'cancelarCambioFotoCria' => 'cancelCriaPhotoChange',
        'confirmarEliminacionFotoCria' => 'confirmCriaPhotoRemoval',
    ];

    public function mount($id = null)
    {
        $this->fechaParto = now()->format('Y-m-d');
        $this->loadMadres();

        if ($id) {
            $this->partoId = $id;
            $this->isEdit = true;
            $part = Parto::with(['madre', 'cria'])->where('fundo_id', session('fundo_id'))->findOrFail($id);

            $this->animalMadreId = $part->animal_madre_id;
            $this->fechaParto = $part->fecha_parto->format('Y-m-d');
            $this->tipoParto = $part->tipo_parto;
            $this->criaSexo = $part->cria_sexo;
            $this->criaNombre = $part->cria?->nombre ?? '';
            $this->criaRazaId = (string) ($part->cria?->raza_id ?? $part->madre?->raza_id ?? '');
            $this->criaPesoNacer = $part->cria_peso_nacer;
            $this->criaEstado = $part->cria_estado;
            $this->condicionMadre = $part->condicion_madre;
            $this->observaciones = $part->observaciones;
            $this->existingCriaFoto = $part->cria?->foto_ruta;
            $this->criaFotoEncuadre = ImageFrame::normalize($part->cria?->foto_encuadre);
            $this->loadRecordPhotos($part);
        }
    }

    public function loadMadres()
    {
        $fundoId = session('fundo_id');
        // Hembras bovinas activas
        $this->madres = Animal::where('fundo_id', $fundoId)
            ->with('especie')
            ->whereHas('especie', function ($q) {
                $q->where('codigo_animal', 'BOV')->where('activo', true);
            })
            ->where('genero', 'hembra')
            ->where('activo', true)
            ->get([
                'id', 'especie_id', 'arete', 'nombre', 'genero', 'fecha_alta',
                'fecha_nacimiento', 'edad_estimada_meses_alta',
            ])
            ->filter(fn (Animal $animal) => $animal->isMatureBovineFemale())
            ->values();
    }

    public function updatedAnimalMadreId($value): void
    {
        $madre = Animal::where('fundo_id', session('fundo_id'))
            ->whereKey($value)
            ->where('genero', 'hembra')
            ->where('activo', true)
            ->whereHas('especie', fn ($query) => $query
                ->where('codigo_animal', 'BOV')
                ->where('activo', true))
            ->with('especie')
            ->first([
                'id', 'especie_id', 'raza_id', 'genero', 'fecha_alta',
                'fecha_nacimiento', 'edad_estimada_meses_alta',
            ]);

        $this->criaRazaId = $madre?->isMatureBovineFemale() ? (string) $madre->raza_id : '';
        $this->resetValidation('criaRazaId');
    }

    public function updatedCriaFoto(): void
    {
        $this->criaPhotoConfirmed = false;
        $this->removeCriaFoto = false;

        if (! $this->criaFoto) {
            return;
        }

        $this->criaFotoEncuadre = ImageFrame::DEFAULT;
        $this->validateOnly('criaFoto', [
            'criaFoto' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ], [
            'criaFoto.image' => 'Selecciona una imagen válida.',
            'criaFoto.mimes' => 'Usa una imagen JPG, PNG o WebP.',
            'criaFoto.max' => 'La imagen optimizada no puede superar 2 MB.',
            'criaFoto.dimensions' => 'La imagen supera las dimensiones permitidas.',
        ]);
        $this->criaPhotoConfirmed = true;
    }

    public function updatedCriaFotoEncuadre(): void
    {
        $this->criaFotoEncuadreChanged = true;
    }

    public function confirmCriaPhotoChange(): void
    {
        if (! $this->criaFoto) {
            return;
        }

        $this->criaPhotoConfirmed = true;
        $this->removeCriaFoto = false;
        $this->resetValidation('criaFoto');
    }

    public function cancelCriaPhotoChange(): void
    {
        $this->reset('criaFoto');
        $this->criaPhotoConfirmed = false;
        $cria = $this->partoId
            ? Parto::where('fundo_id', session('fundo_id'))->with('cria')->find($this->partoId)?->cria
            : null;
        $this->criaFotoEncuadre = ImageFrame::normalize($cria?->foto_encuadre);
        $this->criaFotoEncuadreChanged = false;
        $this->resetValidation('criaFoto');
    }

    public function requestCriaPhotoRemoval(): void
    {
        if (! $this->existingCriaFoto) {
            return;
        }

        $this->dispatch('swal:confirm', [
            'title' => '¿Preparar eliminación?',
            'text' => 'La foto seguirá protegida hasta guardar el parto.',
            'icon' => 'warning',
            'confirmButtonText' => 'Preparar eliminación',
            'cancelButtonText' => 'Mantener foto',
            'event' => 'confirmarEliminacionFotoCria',
        ]);
    }

    public function confirmCriaPhotoRemoval(): void
    {
        $this->reset('criaFoto');
        $this->criaPhotoConfirmed = false;
        $this->removeCriaFoto = true;
        $this->resetValidation('criaFoto');
    }

    public function cancelCriaPhotoRemoval(): void
    {
        $this->removeCriaFoto = false;
    }

    public function save()
    {
        $this->authorizePermission('monitoreo', $this->isEdit ? 'actualizar' : 'crear');

        $fundoId = (int) session('fundo_id');
        $shouldHaveCalf = $this->tipoParto !== 'aborto_prematuro'
            && $this->criaEstado !== 'muerto_al_nacer';
        $bovineSpeciesId = Especie::where('codigo_animal', 'BOV')->where('activo', true)->value('id');
        $motherSpeciesId = Animal::where('fundo_id', $fundoId)
            ->whereKey($this->animalMadreId)
            ->value('especie_id');

        $rules = [
            'animalMadreId' => [
                'required',
                Rule::exists('animales', 'id')->where(fn ($query) => $query
                    ->where('fundo_id', $fundoId)
                    ->where('especie_id', $bovineSpeciesId)
                    ->where('genero', 'hembra')
                    ->where('activo', true)),
                function (string $attribute, mixed $value, \Closure $fail) use ($fundoId): void {
                    $mother = Animal::where('fundo_id', $fundoId)->with('especie')->find($value);
                    if ($mother && ! $mother->isMatureBovineFemale()) {
                        $fail('La madre debe tener al menos '.Animal::MIN_MILKING_AGE_MONTHS.' meses.');
                    }
                    if ($mother?->fecha_nacimiento && filled($this->fechaParto)) {
                        $partoDate = \Carbon\Carbon::parse($this->fechaParto);
                        if ($partoDate->lt($mother->fecha_nacimiento)) {
                            $fail('La fecha del parto no puede ser anterior al nacimiento de la madre ('.$mother->fecha_nacimiento->format('d/m/Y').').');
                        }
                    }
                },
            ],
            'fechaParto' => 'required|date|before_or_equal:today',
            'tipoParto' => 'required|in:normal,asistido,cesarea,aborto_prematuro',
            'criaSexo' => 'required|in:macho,hembra',
            'criaNombre' => 'nullable|string|max:100',
            'criaRazaId' => [
                Rule::requiredIf($shouldHaveCalf),
                'nullable',
                Rule::exists('razas', 'id')->where(fn ($query) => $query
                    ->where('especie_id', $motherSpeciesId)
                    ->where('activo', true)),
            ],
            'criaPesoNacer' => 'nullable|numeric|min:1',
            'criaEstado' => 'required|in:vivo_vigoroso,debil,muerto_al_nacer',
            'condicionMadre' => 'required|in:optima,retencion_placenta,fiebre_leche,desgarro',
            'criaFoto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=6000,max_height=6000',
            'removeCriaFoto' => 'boolean',
            'criaPhotoConfirmed' => 'boolean',
            ...ImageFrame::rules('criaFotoEncuadre'),
            'observaciones' => 'nullable|string|max:5000',
            ...$this->recordPhotoRules(),
        ];

        $this->validate($rules, [
            ...$this->recordPhotoMessages(),
            'animalMadreId.exists' => 'Selecciona una madre bovina activa del fundo.',
            'fechaParto.before_or_equal' => 'La fecha del parto no puede ser futura.',
            'criaRazaId.required' => 'Selecciona la raza de la cría.',
            'criaRazaId.exists' => 'La raza debe pertenecer a la especie de la madre.',
            'criaFoto.image' => 'Selecciona una imagen válida para la cría.',
            'criaFoto.mimes' => 'Usa una imagen JPG, PNG o WebP para la cría.',
            'criaFoto.max' => 'La foto de la cría no puede superar 2 MB optimizada.',
            'criaFoto.dimensions' => 'La foto de la cría supera las dimensiones permitidas.',
        ]);

        $selectedMother = Animal::where('fundo_id', $fundoId)
            ->with('especie')
            ->find($this->animalMadreId);
        if (! $selectedMother?->isMatureBovineFemale()) {
            throw ValidationException::withMessages([
                'animalMadreId' => 'La madre debe ser bovina, hembra y tener al menos '.Animal::MIN_MILKING_AGE_MONTHS.' meses.',
            ]);
        }

        if (! $shouldHaveCalf && $this->criaFoto) {
            throw ValidationException::withMessages([
                'criaFoto' => 'La foto de perfil requiere una cría viva registrada en inventario.',
            ]);
        }
        if ($this->criaFoto && ! $this->criaPhotoConfirmed) {
            throw ValidationException::withMessages([
                'criaFoto' => 'Confirma la nueva imagen antes de guardar.',
            ]);
        }

        $newPhotoPaths = [];
        $newCriaPhoto = null;
        $criaFotoEncuadre = ImageFrame::normalize($this->criaFotoEncuadre);
        $previousCriaPhoto = null;

        try {
            $newPhotoPaths = $this->storeRecordPhotos('monitoreo/partos');
            if ($this->criaFoto) {
                $newCriaPhoto = ImageOptimizer::store($this->criaFoto, 'fotos/animales', 'criaFoto');
            }

            [$parto, $createdCalf, $removedPaths, $previousCriaPhoto] = DB::transaction(function () use ($criaFotoEncuadre, $fundoId, $newCriaPhoto, $newPhotoPaths, $shouldHaveCalf) {
                $parto = $this->isEdit
                    ? Parto::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($this->partoId)
                    : new Parto;
                $madre = Animal::where('fundo_id', $fundoId)
                    ->with('especie')
                    ->whereKey($this->animalMadreId)
                    ->where('genero', 'hembra')
                    ->where('activo', true)
                    ->whereHas('especie', fn ($query) => $query
                        ->where('codigo_animal', 'BOV')
                        ->where('activo', true))
                    ->lockForUpdate()
                    ->firstOrFail();
                if (! $madre->isMatureBovineFemale()) {
                    throw ValidationException::withMessages([
                        'animalMadreId' => 'La madre ya no cumple la edad mínima requerida.',
                    ]);
                }
                $raza = $shouldHaveCalf
                    ? Raza::whereKey($this->criaRazaId)
                        ->where('especie_id', $madre->especie_id)
                        ->where('activo', true)
                        ->firstOrFail()
                    : null;
                $criaId = $parto->cria_animal_id;
                $createdCalf = false;
                $criaNombre = trim((string) $this->criaNombre);
                $previousCriaPhoto = null;

                if ($shouldHaveCalf && ! $criaId) {
                    $allocator = app(AnimalCodeAllocator::class);
                    $birthDate = CarbonImmutable::parse($this->fechaParto);
                    $ageMonths = (int) floor($birthDate->diffInMonths(CarbonImmutable::today()));
                    $code = $allocator->allocate(
                        new Animal,
                        (int) $fundoId,
                        $madre->especie,
                        $birthDate->year
                    );
                    $cria = Animal::create([
                        'fundo_id' => $fundoId,
                        'especie_id' => $madre->especie_id,
                        'raza_id' => $raza->id,
                        ...$code,
                        'nombre' => $criaNombre !== '' ? $criaNombre : 'Cría de '.($madre->nombre ?: $madre->arete),
                        'genero' => $this->criaSexo,
                        'peso' => $this->criaPesoNacer ?: null,
                        'foto_ruta' => $newCriaPhoto,
                        'foto_encuadre' => $newCriaPhoto ? $criaFotoEncuadre : null,
                        'estado_productivo' => Animal::productiveStateForAge($ageMonths),
                        'tipo_alta' => 'parto',
                        'fecha_alta' => $this->fechaParto,
                        'fecha_nacimiento' => $this->fechaParto,
                        'apta_ordeno' => false,
                        'activo' => true,
                    ]);
                    $allocator->record($cria);
                    $criaId = $cria->id;
                    $createdCalf = true;
                } elseif ($shouldHaveCalf && $criaId) {
                    $cria = Animal::where('fundo_id', $fundoId)
                        ->with('especie')
                        ->lockForUpdate()
                        ->findOrFail($criaId);
                    $previousCriaPhoto = $cria->foto_ruta;
                    $photoPath = $newCriaPhoto ?: ($this->removeCriaFoto ? null : $cria->foto_ruta);
                    $cria->fill([
                        'nombre' => $criaNombre !== '' ? $criaNombre : null,
                        'raza_id' => $raza->id,
                        'genero' => $this->criaSexo,
                        'peso' => $this->criaPesoNacer ?: null,
                        'foto_ruta' => $photoPath,
                        'foto_encuadre' => $photoPath
                            ? (($newCriaPhoto || $this->criaFotoEncuadreChanged) ? $criaFotoEncuadre : $cria->foto_encuadre)
                            : null,
                        'fecha_alta' => $this->fechaParto,
                        'fecha_nacimiento' => $this->fechaParto,
                    ]);
                    $cria->estado_productivo = Animal::productiveStateForAge($cria->edadMeses());
                    if (! $cria->canBeMarkedForMilking()) {
                        $cria->apta_ordeno = false;
                    }
                    $cria->save();
                } elseif ($criaId) {
                    $cria = Animal::where('fundo_id', $fundoId)
                        ->lockForUpdate()
                        ->findOrFail($criaId);
                    $previousCriaPhoto = $cria->foto_ruta;
                    $cria->update([
                        'activo' => false,
                        'apta_ordeno' => false,
                        'foto_ruta' => null,
                        'foto_encuadre' => null,
                    ]);
                    $cria->delete();
                    $criaId = null;
                }

                $parto->fill([
                    'fundo_id' => $fundoId,
                    'animal_madre_id' => $this->animalMadreId,
                    'cria_animal_id' => $criaId,
                    'fecha_parto' => $this->fechaParto,
                    'tipo_parto' => $this->tipoParto,
                    'cria_sexo' => $this->criaSexo,
                    'cria_peso_nacer' => $this->criaPesoNacer ?: null,
                    'cria_estado' => $this->criaEstado,
                    'condicion_madre' => $this->condicionMadre,
                    'observaciones' => $this->observaciones ?: null,
                ])->save();

                // Comunicación de datos: la madre pasa a lactante cuando la cría
                // nace viva (o a seca si fue aborto / cría muerta).
                if (! $this->isEdit || $parto->wasRecentlyCreated) {
                    $madre->update([
                        'estado_reproductivo' => $shouldHaveCalf && $this->criaEstado !== 'muerto_al_nacer'
                            ? 'lactante'
                            : 'seca',
                    ]);
                }

                $removedPaths = $this->isEdit ? $this->removeMarkedRecordPhotos($parto) : [];
                $this->attachRecordPhotos($parto, $newPhotoPaths);

                return [$parto, $createdCalf, $removedPaths, $previousCriaPhoto];
            }, attempts: 5);
        } catch (Throwable $exception) {
            $this->deleteStoredRecordPhotos($newPhotoPaths);
            if ($newCriaPhoto) {
                Storage::disk('public')->delete($newCriaPhoto);
            }

            throw $exception;
        }

        $this->deleteUnreferencedRecordPhotos($removedPaths);
        if ($previousCriaPhoto
            && (! $shouldHaveCalf || $newCriaPhoto || $this->removeCriaFoto)
            && $previousCriaPhoto !== $newCriaPhoto
            && ! Animal::withoutGlobalScopes()->where('foto_ruta', $previousCriaPhoto)->exists()) {
            Storage::disk('public')->delete($previousCriaPhoto);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => $this->isEdit ? '¡Actualizado!' : '¡Registrado!',
            'text' => match (true) {
                $this->isEdit => 'Registro de parto actualizado.',
                $createdCalf => 'Parto registrado exitosamente (nueva cría registrada en inventario).',
                default => 'Parto registrado exitosamente.',
            },
        ]);
        $this->publishRecentRecord('monitoreo.partos', $parto);

        return $this->redirectRoute('monitoreo.index', navigate: true);
    }

    public function render()
    {
        $motherSpeciesId = Animal::where('fundo_id', session('fundo_id'))
            ->whereKey($this->animalMadreId)
            ->value('especie_id');
        $razasCria = $motherSpeciesId
            ? Raza::where('especie_id', $motherSpeciesId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
            : collect();

        return view('livewire.monitoreo.parto-form', compact('razasCria'))
            ->layout('layouts.app');
    }
}
