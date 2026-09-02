<?php

namespace App\Livewire\Ajustes\Traits;

use App\Models\ConfiguracionSistema;
use App\Models\DatabaseBackup;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\Backups\FundoDatabaseBackupService;
use App\Services\Security\UserSessionService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\SystemBranding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;
use Throwable;

trait HasSystemSettings
{
    public array $settings = [];

    public string $brandName = '';

    public string $brandTagline = '';

    public string $brandColor = 'emerald';

    public string $brandColorMode = 'preset';

    public string $brandCustomColor = '#718F6D';

    public $brandLogo;

    public array $brandLogoFrame = ImageFrame::DEFAULT;

    #[Locked]
    public bool $brandLogoFrameChanged = false;

    public ?string $brandLogoPath = null;

    public function loadSettings(): void
    {
        $fundoId = $this->fundoId();
        $configs = ConfiguracionSistema::query()->where('fundo_id', $fundoId)->pluck('valor', 'clave');

        $this->settings = [
            'moneda' => $configs->get('moneda', 'PEN'),
            'alerta_dias' => (int) $configs->get('alerta_dias', 7),
            'nombre_fundo' => auth()->user()->fundoActivo()?->nombre ?? '',
        ];
    }

    public function loadBranding(SystemBranding $branding): void
    {
        $this->brandName = $branding->name();
        $this->brandTagline = $branding->tagline();
        $this->brandColor = $branding->color();
        $this->brandColorMode = $branding->colorMode();
        $this->brandCustomColor = $branding->customColor() ?? '#718F6D';
        $this->brandLogoPath = $branding->logoPath();
        $this->brandLogoFrame = $branding->logoFrame();
        $this->brandLogoFrameChanged = false;
        $this->reset('brandLogo');
    }

    public function updatedBrandLogo(): void
    {
        if ($this->brandLogo) {
            $this->brandLogoFrame = ImageFrame::DEFAULT;
        }
    }

    public function updatedBrandLogoFrame(): void
    {
        $this->brandLogoFrameChanged = true;
    }

    public function cancelBrandLogoChange(SystemBranding $branding): void
    {
        $this->reset('brandLogo');
        $this->brandLogoFrame = $branding->logoFrame();
        $this->brandLogoFrameChanged = false;
        $this->resetValidation('brandLogo');
    }


    public function saveSettings(): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $validated = $this->validate([
            'settings.nombre_fundo' => ['required', 'string', 'max:150'],
            'settings.moneda' => ['required', Rule::in(['PEN', 'USD'])],
            'settings.alerta_dias' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        DB::transaction(function () use ($validated): void {
            auth()->user()->fundoActivo()?->update(['nombre' => trim($validated['settings']['nombre_fundo'])]);
            $this->saveConfig('moneda', $validated['settings']['moneda']);
            $this->saveConfig('alerta_dias', (string) $validated['settings']['alerta_dias']);
        });

        app(AuditLogger::class)->record('ajustes.preferencias_actualizadas', 'ajustes', 'Actualizó preferencias del fundo.', metadata: $validated['settings']);

        $this->dispatchSuccess('Preferencias guardadas', 'Configuración del fundo actualizada.');
    }

    public function saveBranding(SystemBranding $branding): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $this->validate([
            'brandName' => ['required', 'string', 'min:2', 'max:80'],
            'brandTagline' => ['required', 'string', 'min:2', 'max:120'],
            'brandColor' => ['required', Rule::in(array_keys(config('branding.palettes', [])))],
            'brandColorMode' => ['required', Rule::in(['preset', 'custom'])],
            'brandCustomColor' => ['required_if:brandColorMode,custom', 'nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brandLogo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:min_width=64,min_height=64,max_width=4000,max_height=4000'],
            ...ImageFrame::rules('brandLogoFrame'),
        ], [
            'brandCustomColor.required_if' => 'Elige un color personalizado.',
            'brandCustomColor.regex' => 'Usa un color hexadecimal válido, por ejemplo #718F6D.',
            'brandLogo.image' => 'Selecciona una imagen válida.',
            'brandLogo.mimes' => 'Usa JPG, PNG o WebP.',
            'brandLogo.max' => 'Logo original máximo: 10 MB.',
            'brandLogo.dimensions' => 'Logo permitido: 64 a 4000 píxeles por lado.',
        ]);

        $oldPath = $branding->logoPath();
        $newPath = null;

        try {
            if ($this->brandLogo) {
                $newPath = ImageOptimizer::store($this->brandLogo, 'branding', 'brandLogo', 512, 256 * 1024, 'public');
            }
            $attributes = [
                'name' => trim($this->brandName),
                'tagline' => trim($this->brandTagline),
                'color' => $this->brandColor,
                'color_mode' => $this->brandColorMode,
                'custom_color' => $this->brandCustomColor,
                'logo_path' => $newPath ?? $oldPath,
            ];
            if ($newPath || $this->brandLogoFrameChanged) {
                $attributes['logo_encuadre'] = ($newPath ?? $oldPath) ? ImageFrame::normalize($this->brandLogoFrame) : null;
            } elseif (! $oldPath) {
                $attributes['logo_encuadre'] = null;
            }
            $branding->save($attributes);
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $branding->invalidate();
        $this->loadBranding($branding);
        $this->dispatch('branding-updated',
            name: $branding->name(),
            tagline: $branding->tagline(),
            palette: $branding->paletteRgb(),
            logoUrl: $branding->logoUrl(),
            logoFrame: $branding->logoFrame(),
        );

        $this->dispatchSuccess('Identidad guardada', 'El diseño y marca del fundo han sido actualizados en todo el sistema.');
        $this->js('window.location.reload()');
        app(AuditLogger::class)->record('ajustes.identidad_actualizada', 'ajustes', 'Actualizó identidad visual.', metadata: [
            'nombre' => $branding->name(),
            'lema' => $branding->tagline(),
            'color' => $branding->color(),
            'modo_color' => $branding->colorMode(),
            'color_personalizado' => $branding->customColor(),
            'logo_actualizado' => (bool) $newPath,
        ]);
        $this->dispatchSuccess('Identidad actualizada', 'Nombre, lema, color y logo aplicados en sistema y reportes.');
    }

    public function removeBrandLogo(SystemBranding $branding): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $oldPath = $branding->logoPath();
        $branding->save(['logo_path' => null, 'logo_encuadre' => null]);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
        $branding->invalidate();
        $this->loadBranding($branding);
        $this->dispatch('branding-updated',
            name: $branding->name(),
            tagline: $branding->tagline(),
            palette: $branding->paletteRgb(),
            logoUrl: null,
            logoFrame: $branding->logoFrame(),
        );
        app(AuditLogger::class)->record('ajustes.logo_retirado', 'ajustes', 'Retiró logo de identidad visual.');
        $this->dispatchSuccess('Logo retirado', 'Se usa nuevamente icono predeterminado.');
        $this->js('window.location.reload()');
    }

}
