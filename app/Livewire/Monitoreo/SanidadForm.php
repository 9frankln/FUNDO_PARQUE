<?php

namespace App\Livewire\Monitoreo;

use App\Models\AlertaProgramada;
use App\Models\Animal;
use App\Models\SanidadRegistro;
use App\Support\ImageFrame;
use App\Traits\AuthorizesPermissions;
use App\Traits\HandlesRecordPhotos;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class SanidadForm extends Component
{
    use AuthorizesPermissions, HandlesRecordPhotos, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $sanId = null;

    #[Locked]
    public $isEdit = false;

    public array $animalIds = [];

    public $fechaEvento = '';

    public $clasificacion = 'enfermedad_infecciosa';

    public $sintomasDiagnostico = '';

    public $tratamiento = '';

    public $medicamentoNombre = '';

    public $dosisVia = '';

    public $estadoClinico = 'en_tratamiento';

    public $animales = [];

    public function mount($id = null)
    {
        $this->fechaEvento = now()->format('Y-m-d');
        $this->loadAnimales();

        if ($id) {
            $this->sanId = $id;
            $this->isEdit = true;
            $san = SanidadRegistro::where('fundo_id', session('fundo_id'))->findOrFail($id);

            $this->animalIds = [(string) $san->animal_id];
            $this->fechaEvento = $san->fecha_evento->format('Y-m-d');
            $this->clasificacion = $san->clasificacion;
            $this->sintomasDiagnostico = $san->sintomas_diagnostico;
            $this->tratamiento = $san->tratamiento;
            $this->medicamentoNombre = $san->medicamento_nombre ?? '';
            $this->dosisVia = $san->dosis_via;
            $this->estadoClinico = $san->estado_clinico;
            $this->loadRecordPhotos($san);
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

    public function save()
    {
        $this->authorizePermission('monitoreo', $this->isEdit ? 'actualizar' : 'crear');

        $this->sintomasDiagnostico = $this->normalizeText($this->sintomasDiagnostico);
        $this->tratamiento = $this->normalizeText($this->tratamiento);
        $this->dosisVia = $this->normalizeText($this->dosisVia);

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $sanidad = $this->sanId
            ? SanidadRegistro::where('fundo_id', $fundoId)->findOrFail($this->sanId)
            : new SanidadRegistro;

        $rules = [
            'animalIds' => $this->isEdit
                ? ['required', 'array', 'size:1']
                : ['required', 'array', 'min:1'],
            'animalIds.*' => [
                'required',
                'distinct',
                Rule::exists('animales', 'id')->where(fn ($query) => $query->where('fundo_id', $fundoId)),
            ],
            'fechaEvento' => 'required|date',
            'clasificacion' => 'required|in:enfermedad_infecciosa,trastorno_metabolico,lesion_accidente',
            'sintomasDiagnostico' => 'required|string',
            'tratamiento' => 'nullable|string',
            'medicamentoNombre' => 'nullable|string|max:150',
            'dosisVia' => 'nullable|string|max:100',
            'estadoClinico' => 'required|in:en_tratamiento,recuperada,critico,cuarentena,baja',
            ...$this->recordPhotoRules(),
        ];

        $this->validate($rules, $this->validationMessages());

        $data = [
            'fundo_id' => $fundoId,
            'fecha_evento' => $this->fechaEvento,
            'clasificacion' => $this->clasificacion,
            'sintomas_diagnostico' => $this->sintomasDiagnostico,
            'tratamiento' => $this->tratamiento ?: null,
            'medicamento_nombre' => $this->medicamentoNombre ?: null,
            'dosis_via' => $this->dosisVia ?: null,
            'estado_clinico' => $this->estadoClinico,
        ];

        $newPhotoPaths = [];

        try {
            $newPhotoPaths = $this->storeRecordPhotos('monitoreo/sanidad');

            [$savedRecords, $removedPaths] = DB::transaction(function () use ($data, $fundoId, $sanidad, $newPhotoPaths) {
                $records = collect();
                $removedPaths = $this->isEdit ? $this->removeMarkedRecordPhotos($sanidad) : [];
                $animals = Animal::where('fundo_id', $fundoId)
                    ->whereIn('id', $this->animalIds)
                    ->get()
                    ->keyBy(fn (Animal $animal) => (string) $animal->id);

                foreach ($this->animalIds as $index => $animalId) {
                    $record = $this->isEdit && $index === 0 ? $sanidad : new SanidadRegistro;
                    $record->fill([...$data, 'animal_id' => $animalId])->save();
                    $this->attachRecordPhotos($record, $newPhotoPaths);
                    $records->push($record);

                    if ($this->estadoClinico === 'cuarentena' && ! $this->isEdit) {
                        $animal = $animals->get((string) $animalId);
                        AlertaProgramada::create([
                            'fundo_id' => $fundoId,
                            'animal_id' => $animalId,
                            'tipo' => 'cuarentena',
                            'fecha_alerta' => now()->addDays(7)->format('Y-m-d'),
                            'mensaje' => 'Control de cuarentena (7 días cumplidos) para el animal con código '.$animal->arete,
                        ]);
                    }
                }

                return [$records, $removedPaths];
            });
        } catch (Throwable $exception) {
            $this->deleteStoredRecordPhotos($newPhotoPaths);

            throw $exception;
        }

        $this->deleteUnreferencedRecordPhotos($removedPaths);

        $count = $savedRecords->count();
        session()->flash('success', $this->isEdit
            ? 'Ficha médica del animal actualizada.'
            : ($count === 1 ? 'Evento clínico registrado.' : "Evento clínico registrado para {$count} animales."));
        $this->publishRecentRecord('monitoreo.sanidad', $savedRecords->first());

        return redirect()->route('monitoreo.index');
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return mb_strtolower(trim($value), 'UTF-8');
    }

    private function validationMessages(): array
    {
        return array_merge([
            'animalIds.required' => 'Selecciona al menos un animal.',
            'animalIds.min' => 'Selecciona al menos un animal.',
            'animalIds.size' => 'En edición solo puedes mantener un animal por evento.',
            'animalIds.*.exists' => 'La selección contiene un animal no válido.',
            'animalIds.*.distinct' => 'No puedes seleccionar el mismo animal dos veces.',
        ], $this->recordPhotoMessages());
    }

    public function render()
    {
        return view('livewire.monitoreo.sanidad-form')
            ->layout('layouts.app');
    }
}
