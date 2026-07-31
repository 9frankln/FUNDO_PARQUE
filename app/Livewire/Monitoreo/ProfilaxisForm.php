<?php

namespace App\Livewire\Monitoreo;

use App\Models\AlertaProgramada;
use App\Models\Animal;
use App\Models\ProfilaxisDosisProgramada;
use App\Models\ProfilaxisRegistro;
use App\Support\ImageFrame;
use App\Traits\AuthorizesPermissions;
use App\Traits\HandlesRecordPhotos;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ProfilaxisForm extends Component
{
    use AuthorizesPermissions, HandlesRecordPhotos, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $profId = null;

    #[Locked]
    public $isEdit = false;

    public $fechaAplicacion = '';

    public $tipoIntervencion = 'vacuna';

    public $proposito = '';

    public $productoMarca = '';

    public $dosis = '';

    public array $dosisProgramadas = [];

    public $responsable = '';

    public $observaciones = '';

    public $selectedAnimals = [];

    public $animales = [];

    public function mount($id = null)
    {
        $this->fechaAplicacion = now()->format('Y-m-d');
        $this->loadAnimales();

        if ($id) {
            $this->profId = $id;
            $this->isEdit = true;
            $prof = ProfilaxisRegistro::with(['animales', 'dosisProgramadas'])
                ->where('fundo_id', session('fundo_id'))
                ->findOrFail($id);

            $this->fechaAplicacion = $prof->fecha_aplicacion->format('Y-m-d');
            $this->tipoIntervencion = $prof->tipo_intervencion;
            $this->proposito = $prof->proposito;
            $this->productoMarca = $prof->producto_marca;
            $this->dosis = $prof->dosis;
            $this->dosisProgramadas = $prof->dosisProgramadas
                ->map(fn ($dose) => ['fecha' => $dose->fecha_programada->format('Y-m-d')])
                ->values()
                ->all();
            if ($this->dosisProgramadas === [] && $prof->proxima_dosis) {
                $this->dosisProgramadas = [['fecha' => $prof->proxima_dosis->format('Y-m-d')]];
            }
            $this->responsable = $prof->responsable;
            $this->observaciones = $prof->observaciones;

            $this->selectedAnimals = $prof->animales->pluck('id')->map(fn ($id) => (string) $id)->all();
            $this->loadRecordPhotos($prof);
        }
    }

    public function loadAnimales()
    {
        $fundoId = session('fundo_id');
        $this->animales = Animal::where('fundo_id', $fundoId)
            ->where('activo', true)
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
            ])
            ->values()
            ->all();
    }

    public function addDoseDate(): void
    {
        $this->dosisProgramadas[] = ['fecha' => ''];
    }

    public function removeDoseDate(int $index): void
    {
        if (! array_key_exists($index, $this->dosisProgramadas)) {
            return;
        }

        unset($this->dosisProgramadas[$index]);
        $this->dosisProgramadas = array_values($this->dosisProgramadas);
        $this->resetValidation('dosisProgramadas');
    }

    public function save()
    {
        $this->authorizePermission('monitoreo', $this->isEdit ? 'actualizar' : 'crear');

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $rules = [
            'fechaAplicacion' => 'required|date|before_or_equal:today',
            'tipoIntervencion' => 'required|in:vacuna,desparasitante_interno,desparasitante_externo,vitamina',
            'productoMarca' => 'required|string|max:100',
            'proposito' => 'nullable|string|max:255',
            'dosis' => 'nullable|string|max:50',
            'dosisProgramadas' => 'array|max:12',
            'dosisProgramadas.*.fecha' => 'bail|required|date|after:fechaAplicacion|distinct',
            'responsable' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:5000',
            'selectedAnimals' => 'required|array|min:1',
            'selectedAnimals.*' => [
                'required',
                'distinct',
                Rule::exists('animales', 'id')->where(fn ($query) => $query
                    ->where('fundo_id', $fundoId)
                    ->where('activo', true)),
            ],
            ...$this->recordPhotoRules(),
        ];

        $this->validate($rules, [
            ...$this->recordPhotoMessages(),
            'fechaAplicacion.before_or_equal' => 'La fecha de aplicación no puede ser futura.',
            'dosisProgramadas.max' => 'Puedes programar máximo 12 dosis futuras.',
            'dosisProgramadas.*.fecha.required' => 'Completa la fecha o elimina esta dosis.',
            'dosisProgramadas.*.fecha.after' => 'Cada dosis futura debe ser posterior a la aplicación.',
            'dosisProgramadas.*.fecha.distinct' => 'No repitas fechas de dosis.',
            'selectedAnimals.required' => 'Selecciona al menos un animal.',
            'selectedAnimals.min' => 'Selecciona al menos un animal.',
            'selectedAnimals.*.distinct' => 'No repitas animales.',
            'selectedAnimals.*.exists' => 'La selección contiene un animal no disponible.',
        ]);

        $scheduledDates = collect($this->dosisProgramadas)
            ->pluck('fecha')
            ->map(fn ($date) => (string) $date)
            ->values()
            ->all();
        $previousDate = $this->fechaAplicacion;
        foreach ($scheduledDates as $index => $date) {
            if ($date <= $previousDate) {
                throw ValidationException::withMessages([
                    'dosisProgramadas.'.$index.'.fecha' => 'Las dosis deben estar ordenadas cronológicamente.',
                ]);
            }
            $previousDate = $date;
        }

        $animalIds = array_values(array_unique(array_map('intval', $this->selectedAnimals)));
        $newPhotoPaths = [];

        try {
            $newPhotoPaths = $this->storeRecordPhotos('monitoreo/profilaxis');

            [$profilaxis, $removedPaths] = DB::transaction(function () use ($animalIds, $fundoId, $newPhotoPaths, $scheduledDates) {
                $profilaxis = $this->isEdit
                    ? ProfilaxisRegistro::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($this->profId)
                    : new ProfilaxisRegistro;
                $profilaxis->fill([
                    'fundo_id' => $fundoId,
                    'alcance' => count($animalIds) === 1 ? 'individual' : 'lote',
                    'fecha_aplicacion' => $this->fechaAplicacion,
                    'tipo_intervencion' => $this->tipoIntervencion,
                    'producto_marca' => $this->productoMarca,
                    'proposito' => $this->proposito ?: null,
                    'dosis' => $this->dosis ?: null,
                    'proxima_dosis' => $scheduledDates[0] ?? null,
                    'responsable' => $this->responsable ?: null,
                    'observaciones' => $this->observaciones ?: null,
                ])->save();

                $profilaxis->animales()->sync($animalIds);
                $existingDoses = $profilaxis->dosisProgramadas()->get()
                    ->keyBy(fn (ProfilaxisDosisProgramada $dose) => $dose->fecha_programada->format('Y-m-d'));
                $doseIds = [];
                foreach ($scheduledDates as $date) {
                    $dose = $existingDoses->get($date)
                        ?? $profilaxis->dosisProgramadas()->create(['fecha_programada' => $date]);
                    $doseIds[] = $dose->id;
                }

                $obsoleteDoseIds = $existingDoses->pluck('id')->diff($doseIds)->all();
                if ($obsoleteDoseIds !== []) {
                    ProfilaxisDosisProgramada::whereIn('id', $obsoleteDoseIds)->delete();
                }

                if ($doseIds !== []) {
                    AlertaProgramada::withoutGlobalScopes()
                        ->whereIn('profilaxis_dosis_id', $doseIds)
                        ->whereNotIn('animal_id', $animalIds)
                        ->delete();
                }
                $animals = Animal::where('fundo_id', $fundoId)
                    ->whereIn('id', $animalIds)
                    ->get(['id', 'arete'])
                    ->keyBy('id');
                foreach ($scheduledDates as $index => $date) {
                    $doseId = $doseIds[$index];
                    foreach ($animalIds as $animalId) {
                        $animal = $animals->get($animalId);
                        $alert = AlertaProgramada::withoutGlobalScopes()->firstOrNew([
                            'profilaxis_dosis_id' => $doseId,
                            'animal_id' => $animalId,
                        ]);
                        $alert->fill([
                            'fundo_id' => $fundoId,
                            'tipo' => 'proxima_dosis',
                            'fecha_alerta' => $date,
                            'mensaje' => 'Dosis '.($index + 2).' de '.$this->productoMarca.' ('.str_replace('_', ' ', $this->tipoIntervencion).') para '.$animal->arete,
                        ])->save();
                    }
                }

                $removedPaths = $this->isEdit ? $this->removeMarkedRecordPhotos($profilaxis) : [];
                $this->attachRecordPhotos($profilaxis, $newPhotoPaths);

                return [$profilaxis, $removedPaths];
            }, attempts: 5);
        } catch (Throwable $exception) {
            $this->deleteStoredRecordPhotos($newPhotoPaths);

            throw $exception;
        }

        $this->deleteUnreferencedRecordPhotos($removedPaths);

        session()->flash('success', $this->isEdit ? 'Registro de profilaxis actualizado.' : 'Intervención de profilaxis registrada.');
        $this->publishRecentRecord('monitoreo.profilaxis', $profilaxis);

        return redirect()->route('monitoreo.index');
    }

    public function render()
    {
        return view('livewire.monitoreo.profilaxis-form')
            ->layout('layouts.app');
    }
}
