<?php

namespace App\Livewire\Animal;

use App\Models\Animal;
use App\Models\Especie;
use App\Models\Parto;
use App\Models\Raza;
use App\Support\AnimalCodeAllocator;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Form extends Component
{
    use AuthorizesPermissions, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $animalId = null;

    #[Locked]
    public $isEdit = false;

    public $codigoNumero = '';

    #[Locked]
    public $codigoPrefijo = '';

    #[Locked]
    public $codigoAnio = null;

    #[Locked]
    public $codigoSugerido = null;

    #[Locked]
    public $originalSpeciesId = null;

    public $nombre = '';

    public $especieId = '';

    public $razaId = '';

    public $genero = 'hembra';

    public $peso = '';

    public $foto = null;

    public array $fotoEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $fotoEncuadreChanged = false;

    #[Locked]
    public $existingFoto = null;

    public bool $removeFoto = false;

    public bool $photoConfirmed = false;

    public $estadoReproductivo = '';

    public $tipoAlta = 'compra';

    public $precioCompra = '';

    public $fechaAlta = '';

    public $fechaNacimiento = '';

    public $edadEstimadaAnios = '';

    public $edadEstimadaMeses = '';

    public $aptaOrdeno = false;

    public $activo = true;

    public $observaciones = '';

    protected $listeners = [
        'confirmarCambioFotoAnimal' => 'confirmPhotoChange',
        'cancelarCambioFotoAnimal' => 'cancelPhotoChange',
        'confirmarEliminacionFotoAnimal' => 'confirmPhotoRemoval',
    ];

    public function mount($id = null)
    {
        $this->fechaAlta = now()->format('Y-m-d');

        if ($id) {
            $this->animalId = $id;
            $this->isEdit = true;
            $animal = Animal::where('fundo_id', session('fundo_id'))->findOrFail($id);

            $this->codigoPrefijo = $animal->codigo_prefijo ?: '';
            $this->codigoAnio = $animal->codigo_anio;
            $this->codigoSugerido = $animal->codigo_secuencia;
            $this->codigoNumero = $animal->codigo_secuencia
                ? str_pad((string) $animal->codigo_secuencia, 3, '0', STR_PAD_LEFT)
                : '';
            $this->nombre = $animal->nombre;
            $this->especieId = $animal->especie_id;
            $this->originalSpeciesId = $animal->especie_id;
            $this->razaId = $animal->raza_id;
            $this->genero = $animal->genero;
            $this->peso = $animal->peso;
            $this->existingFoto = $animal->foto_ruta;
            $this->fotoEncuadre = ImageFrame::normalize($animal->foto_encuadre);
            $this->estadoReproductivo = $animal->estado_reproductivo;
            $this->tipoAlta = $animal->tipo_alta;
            $this->precioCompra = $animal->precio_compra ?? '';
            $this->fechaAlta = $animal->fecha_alta->format('Y-m-d');
            $this->fechaNacimiento = $animal->fecha_nacimiento?->format('Y-m-d') ?: '';
            $estimatedMonths = (int) ($animal->edad_estimada_meses_alta ?? 0);
            $this->edadEstimadaAnios = $animal->edad_estimada_meses_alta !== null && intdiv($estimatedMonths, 12) > 0
                ? intdiv($estimatedMonths, 12)
                : '';
            $this->edadEstimadaMeses = $animal->edad_estimada_meses_alta !== null && $estimatedMonths % 12 > 0
                ? $estimatedMonths % 12
                : '';
            $this->aptaOrdeno = $animal->apta_ordeno;
            $this->activo = $animal->activo;
            $this->observaciones = $animal->observaciones;
        }

        $this->normalizeMilkingSelection();
    }

    public function updatedEspecieId()
    {
        $this->razaId = '';
        $this->normalizeMilkingSelection();

        $this->refreshCodePreview();
    }

    public function updatedGenero()
    {
        $this->normalizeMilkingSelection();
    }

    public function updatedFechaAlta(): void
    {
        if ($this->tipoAlta === 'parto') {
            $this->fechaNacimiento = $this->fechaAlta;
        }

        if (! $this->isEdit || (int) $this->especieId !== (int) $this->originalSpeciesId) {
            $this->refreshCodePreview();
        }

        $this->normalizeMilkingSelection();
    }

    public function updatedFechaNacimiento($value): void
    {
        if ($this->tipoAlta === 'parto') {
            $this->fechaAlta = $value;
        }

        $this->normalizeMilkingSelection();
    }

    public function updatedTipoAlta($value): void
    {
        if ($value === 'parto') {
            $this->fechaNacimiento = $this->fechaAlta;
            $this->edadEstimadaAnios = '';
            $this->edadEstimadaMeses = '';
            $this->aptaOrdeno = false;
        } else {
            $this->fechaNacimiento = '';
        }

        if ($value !== 'compra') {
            $this->precioCompra = '';
        }

        $this->normalizeMilkingSelection();
    }

    public function updatedEdadEstimadaAnios(): void
    {
        $this->normalizeMilkingSelection();
    }

    public function updatedEdadEstimadaMeses(): void
    {
        $this->normalizeMilkingSelection();
    }

    public function updatedCodigoNumero($value): void
    {
        $digits = substr(preg_replace('/\D+/', '', (string) $value), 0, 3);
        $this->codigoNumero = $digits === ''
            ? ''
            : str_pad((string) (int) $digits, 3, '0', STR_PAD_LEFT);
    }

    #[Computed]
    public function codigoPreview(): string
    {
        if (! $this->codigoPrefijo || ! $this->codigoAnio || $this->codigoNumero === '') {
            return 'Selecciona un tipo de animal';
        }

        return AnimalCodeAllocator::format(
            $this->codigoPrefijo,
            (int) $this->codigoAnio,
            (int) $this->codigoNumero
        );
    }

    #[Computed]
    public function edadPreview(): string
    {
        $animal = $this->previewAnimal();

        return $animal->edad_texto;
    }

    #[Computed]
    public function clasificacionPreview(): string
    {
        return $this->previewAnimal()->clasificacion_edad;
    }

    #[Computed]
    public function denticionPreview(): ?string
    {
        return $this->previewAnimal()->denticion_estimada;
    }

    #[Computed]
    public function showMilkingOption(): bool
    {
        return $this->previewAnimal()->isBovineFemale();
    }

    #[Computed]
    public function canEnableMilking(): bool
    {
        return $this->previewAnimal()->canBeMarkedForMilking();
    }

    #[Computed]
    public function milkingEligibilityMessage(): string
    {
        if ($this->canEnableMilking()) {
            return 'Cumple edad mínima. Activa esta opción solo si está en producción láctea.';
        }

        return 'Disponible al cumplir '.Animal::MIN_MILKING_AGE_MONTHS.' meses.';
    }

    #[Computed]
    public function admissionDateLabel(): string
    {
        return match ($this->tipoAlta) {
            'compra' => 'Fecha de compra',
            'parto' => 'Fecha de nacimiento',
            'donacion' => 'Fecha de recepción',
            'traslado' => 'Fecha de traslado',
            'prestamo' => 'Inicio del préstamo',
            default => 'Fecha de ingreso',
        };
    }

    #[Computed]
    public function weightLabel(): string
    {
        return match ($this->tipoAlta) {
            'compra' => 'Peso al comprar (kg)',
            'parto' => 'Peso al nacer (kg)',
            'donacion' => 'Peso al recibir (kg)',
            'traslado' => 'Peso al trasladar (kg)',
            'prestamo' => 'Peso al iniciar préstamo (kg)',
            default => 'Peso de ingreso (kg)',
        };
    }

    public function updatedFoto(): void
    {
        $this->photoConfirmed = false;
        $this->removeFoto = false;

        if (! $this->foto) {
            return;
        }

        $this->fotoEncuadre = ImageFrame::DEFAULT;
        $this->validateOnly('foto', [
            'foto' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ], [
            'foto.image' => 'Selecciona una imagen válida.',
            'foto.mimes' => 'Usa una imagen JPG, PNG o WebP.',
            'foto.max' => 'La imagen optimizada no puede superar 2 MB.',
            'foto.dimensions' => 'La imagen supera las dimensiones permitidas.',
        ]);
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
    }

    public function cancelPhotoChange(): void
    {
        $this->reset('foto');
        $this->photoConfirmed = false;
        $this->fotoEncuadre = $this->animalId
            ? ImageFrame::normalize(Animal::where('fundo_id', session('fundo_id'))->find($this->animalId)?->foto_encuadre)
            : ImageFrame::DEFAULT;
        $this->fotoEncuadreChanged = false;
        $this->resetValidation('foto');
    }

    public function requestPhotoRemoval(): void
    {
        if (! $this->existingFoto) {
            return;
        }

        $this->dispatch('swal:confirm', [
            'title' => '¿Preparar eliminación?',
            'text' => 'La foto seguirá protegida hasta guardar. Podrás deshacer esta acción.',
            'icon' => 'warning',
            'confirmButtonText' => 'Preparar eliminación',
            'cancelButtonText' => 'Mantener foto',
            'event' => 'confirmarEliminacionFotoAnimal',
        ]);
    }

    public function confirmPhotoRemoval(): void
    {
        $this->reset('foto');
        $this->photoConfirmed = false;
        $this->removeFoto = true;
        $this->resetValidation('foto');
    }

    public function cancelPhotoRemoval(): void
    {
        $this->removeFoto = false;
    }

    public function save()
    {
        $this->authorizePermission('animal', $this->isEdit ? 'actualizar' : 'crear');

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $animal = $this->animalId
            ? Animal::where('fundo_id', $fundoId)->findOrFail($this->animalId)
            : new Animal;

        if ($this->tipoAlta === 'parto') {
            $this->fechaAlta = $this->fechaNacimiento;
            $this->precioCompra = '';
            $this->edadEstimadaAnios = '';
            $this->edadEstimadaMeses = '';
        }

        $this->updatedCodigoNumero($this->codigoNumero);
        $rules = [
            'especieId' => [
                'required',
                Rule::exists('especies', 'id')->where(fn ($query) => $query
                    ->where('activo', true)
                    ->whereNotNull('codigo_animal')),
            ],
            'razaId' => [
                'required',
                Rule::exists('razas', 'id')->where(fn ($query) => $query
                    ->where('especie_id', $this->especieId)
                    ->where('activo', true)),
            ],
            'codigoNumero' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
            'nombre' => 'nullable|string|max:100',
            'genero' => 'required|in:macho,hembra',
            'peso' => 'nullable|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=6000,max_height=6000',
            'removeFoto' => 'boolean',
            'photoConfirmed' => 'boolean',
            ...ImageFrame::rules('fotoEncuadre'),
            'estadoReproductivo' => ['nullable', Rule::in(array_keys(Animal::REPRODUCTIVE_STATES))],
            'tipoAlta' => 'required|in:compra,parto,donacion,traslado,prestamo',
            'precioCompra' => $this->tipoAlta === 'compra'
                ? ['required', 'numeric', 'min:0.01', 'max:9999999999.99']
                : ['nullable'],
            'aptaOrdeno' => 'boolean',
            'activo' => 'boolean',
            'observaciones' => 'nullable|string|max:5000',
        ];

        if ($this->tipoAlta === 'parto') {
            $rules['fechaNacimiento'] = 'required|date|before_or_equal:today';
        } else {
            $rules['fechaAlta'] = 'required|date|before_or_equal:today';
            $rules['edadEstimadaAnios'] = 'nullable|required_without:edadEstimadaMeses|integer|min:0|max:100';
            $rules['edadEstimadaMeses'] = 'nullable|required_without:edadEstimadaAnios|integer|min:0|max:11';
        }

        $this->validate($rules, [
            'codigoNumero.required' => 'Selecciona la especie para generar el código.',
            'codigoNumero.regex' => 'La numeración debe contener tres dígitos.',
            'codigoNumero.not_in' => 'La numeración debe iniciar en 001.',
            'razaId.exists' => 'La raza no pertenece a la especie seleccionada.',
            'fechaAlta.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
            'fechaNacimiento.required' => 'Registra la fecha de nacimiento.',
            'fechaNacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'precioCompra.required' => 'Registra el precio pagado por el animal.',
            'precioCompra.min' => 'El precio de compra debe ser mayor que cero.',
            'edadEstimadaAnios.required_without' => 'Indica al menos los años o meses aproximados.',
            'edadEstimadaMeses.required_without' => 'Indica al menos los años o meses aproximados.',
            'foto.image' => 'Selecciona una imagen válida.',
            'foto.mimes' => 'Usa una imagen JPG, PNG o WebP.',
            'foto.max' => 'La imagen optimizada no puede superar 2 MB.',
            'foto.dimensions' => 'La imagen supera las dimensiones permitidas.',
        ]);

        if ($this->foto && ! $this->photoConfirmed) {
            throw ValidationException::withMessages([
                'foto' => 'Confirma la nueva imagen antes de guardar.',
            ]);
        }

        $previousPhoto = $animal->foto_ruta;
        $newPhoto = null;
        $fotoEncuadre = ImageFrame::normalize($this->fotoEncuadre);
        $species = Especie::findOrFail($this->especieId);
        $sameSpecies = $animal->exists && (int) $animal->especie_id === (int) $species->id;
        $codeYear = $sameSpecies && $animal->codigo_anio
            ? (int) $animal->codigo_anio
            : now()->year;
        $number = (int) $this->codigoNumero;
        $requestedNumber = $this->codigoSugerido !== null && $number === (int) $this->codigoSugerido
            ? null
            : $number;
        $estimatedAge = $this->tipoAlta === 'parto'
            ? null
            : ((int) ($this->edadEstimadaAnios ?: 0) * 12) + (int) ($this->edadEstimadaMeses ?: 0);
        $birthDate = $this->tipoAlta === 'parto' ? $this->fechaNacimiento : null;
        $previewAnimal = $this->previewAnimal();
        $currentAge = $previewAnimal->edadMeses();
        $milkingEligible = $previewAnimal->canBeMarkedForMilking();
        if (! $milkingEligible) {
            $this->aptaOrdeno = false;
        }
        if ($previewAnimal->isBovineFemale() && ! $milkingEligible) {
            $this->estadoReproductivo = '';
        }
        $productiveState = $milkingEligible && $this->estadoReproductivo === 'en_produccion'
            ? 'produccion'
            : Animal::productiveStateForAge($currentAge);
        $allocator = app(AnimalCodeAllocator::class);

        try {
            if ($this->foto) {
                $newPhoto = ImageOptimizer::store($this->foto, 'fotos/animales');
            }

            DB::transaction(function () use (
                $animal,
                $allocator,
                $birthDate,
                $codeYear,
                $estimatedAge,
                $fundoId,
                $fotoEncuadre,
                $milkingEligible,
                $newPhoto,
                $productiveState,
                $requestedNumber,
                $species
            ) {
                $target = $animal->exists
                    ? Animal::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($animal->id)
                    : $animal;
                $code = $allocator->allocate(
                    $target,
                    $fundoId,
                    $species,
                    $codeYear,
                    $requestedNumber
                );
                $photoPath = $newPhoto ?: ($this->removeFoto ? null : $target->foto_ruta);
                $target->fill([
                    'fundo_id' => $fundoId,
                    'especie_id' => $this->especieId,
                    'raza_id' => $this->razaId,
                    ...$code,
                    'nombre' => $this->nombre ?: null,
                    'genero' => $this->genero,
                    'peso' => $this->peso ?: null,
                    'foto_ruta' => $photoPath,
                    ...($photoPath === null
                        ? ['foto_encuadre' => null]
                        : (($newPhoto || $this->fotoEncuadreChanged) ? ['foto_encuadre' => $fotoEncuadre] : [])),
                    'estado_productivo' => $productiveState,
                    'estado_reproductivo' => $this->genero === 'hembra'
                        ? ($this->estadoReproductivo ?: null)
                        : null,
                    'tipo_alta' => $this->tipoAlta,
                    'precio_compra' => $this->tipoAlta === 'compra' ? $this->precioCompra : null,
                    'fecha_alta' => $this->fechaAlta,
                    'fecha_nacimiento' => $birthDate,
                    'edad_estimada_meses_alta' => $estimatedAge,
                    'apta_ordeno' => $milkingEligible && $this->aptaOrdeno,
                    'activo' => $this->activo,
                    'observaciones' => $this->observaciones ?: null,
                ])->save();
                $allocator->record($target);

                Parto::where('fundo_id', $fundoId)
                    ->where('cria_animal_id', $target->id)
                    ->lockForUpdate()
                    ->get()
                    ->each(function (Parto $parto) use ($target) {
                        $parto->cria_sexo = $target->genero;
                        $parto->cria_peso_nacer = $target->peso;
                        if ($target->fecha_nacimiento) {
                            $parto->fecha_parto = $target->fecha_nacimiento;
                        }
                        $parto->save();
                    });
            }, attempts: 5);
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($previousPhoto && ($newPhoto || $this->removeFoto) && $previousPhoto !== $newPhoto) {
            Storage::disk('public')->delete($previousPhoto);
        }

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => $this->isEdit ? '¡Actualizado!' : '¡Registrado!',
            'text' => $this->isEdit ? 'Animal modificado correctamente.' : 'Animal registrado correctamente.',
        ]);

        $this->publishRecentRecord('animal.animales', $animal);

        return redirect()->route('animal.index');
    }

    public function render()
    {
        $especies = Especie::where('activo', true)
            ->whereNotNull('codigo_animal')
            ->orderBy('nombre')
            ->get();
        $razas = $this->especieId
            ? Raza::where('especie_id', $this->especieId)->where('activo', true)->get()
            : [];

        return view('livewire.animal.form', compact('especies', 'razas'))
            ->layout('layouts.app');
    }

    private function refreshCodePreview(): void
    {
        $species = Especie::where('activo', true)->find($this->especieId);
        if (! $species?->codigo_animal) {
            $this->codigoPrefijo = '';
            $this->codigoAnio = null;
            $this->codigoSugerido = null;
            $this->codigoNumero = '';

            return;
        }

        if ($this->isEdit && (int) $this->especieId === (int) $this->originalSpeciesId) {
            $animal = Animal::where('fundo_id', session('fundo_id'))->find($this->animalId);
            if ($animal?->codigo_secuencia) {
                $this->codigoPrefijo = $animal->codigo_prefijo;
                $this->codigoAnio = $animal->codigo_anio;
                $this->codigoSugerido = $animal->codigo_secuencia;
                $this->codigoNumero = str_pad((string) $animal->codigo_secuencia, 3, '0', STR_PAD_LEFT);

                return;
            }
        }

        $year = now()->year;

        $suggested = app(AnimalCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $species->id,
            $year
        );
        $this->codigoPrefijo = $species->codigo_animal;
        $this->codigoAnio = $year;
        $this->codigoSugerido = $suggested;
        $this->codigoNumero = str_pad((string) $suggested, 3, '0', STR_PAD_LEFT);
    }

    private function normalizeMilkingSelection(): void
    {
        $animal = $this->previewAnimal();
        if (! $animal->canBeMarkedForMilking()) {
            $this->aptaOrdeno = false;
        }
        if ($animal->isBovineFemale() && ! $animal->isMatureBovineFemale()) {
            $this->estadoReproductivo = '';
        }
    }

    private function previewAnimal(): Animal
    {
        $hasEstimatedAge = $this->edadEstimadaAnios !== '' || $this->edadEstimadaMeses !== '';
        $estimatedAge = $this->tipoAlta === 'parto' || ! $hasEstimatedAge
            ? null
            : ((int) ($this->edadEstimadaAnios ?: 0) * 12) + (int) ($this->edadEstimadaMeses ?: 0);
        $animal = new Animal([
            'genero' => $this->genero,
            'fecha_alta' => $this->fechaAlta ?: now()->toDateString(),
            'fecha_nacimiento' => $this->tipoAlta === 'parto' && $this->fechaNacimiento
                ? $this->fechaNacimiento
                : null,
            'edad_estimada_meses_alta' => $estimatedAge,
        ]);
        $animal->setRelation('especie', Especie::find($this->especieId));

        return $animal;
    }
}
