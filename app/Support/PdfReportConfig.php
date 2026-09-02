<?php

namespace App\Support;

use App\Models\ConfiguracionSistema;
use App\Models\Fundo;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Throwable;

class PdfReportConfig
{
    private const DEFAULTS = [
        'mostrar_marca_agua' => 'true',
        'orientacion_marca_agua' => 'diagonal',
        'texto_marca_agua' => '',
        'opacidad_marca_agua' => '0.04',
        'color_marca_agua' => '#064e3b',
        'mostrar_logo' => 'true',
        'estilo_color' => 'emerald',
        'color_acento' => '#047857',
        'modo_color_tablas' => 'multi',
        'paleta_tablas' => 'emerald,indigo,amber,sky,rose,slate,teal,violet',
        'estilo_esquinas' => 'redondeado',
        'radio_esquinas' => '5',
        'mostrar_pie' => 'true',
        'texto_pie' => '',
        'mostrar_num_pagina' => 'true',
        'mostrar_fecha_hora' => 'true',
        'mostrar_generado_por' => 'true',
        'mostrar_firmas' => 'true',
        'mostrar_firma_1' => 'true',
        'mostrar_firma_2' => 'true',
        'tipo_firma' => 'digital',
        'escala_firmas' => '100',
        'firma_1_cargo' => 'Responsable de Fundo / Titular',
        'firma_1_nombre' => 'Noe Franklin Choquenaira Quispe',
        'firma_1_documento' => 'DNI 74056499',
        'firma_2_cargo' => 'Médico Veterinario / Control Técnico',
        'firma_2_nombre' => 'Supervisión Técnica y Sanitaria',
        'firma_2_documento' => 'DNI / CMVP Colegiatura',
        'firma_motivo' => 'Autorización y Conformidad del Documento',
        'firma_software' => 'AGROFUNDO ERP v2.6 · Software Ganadero',
        'firma_mostrar_hash' => 'true',
        'mostrar_sello_autenticidad' => 'true',
        'mostrar_sello_externo' => 'false',
        'sello_externo_entidad' => 'Entidad Financiera / Bancaria',
        'sello_externo_cargo' => 'Analista de Crédito / Auditor Técnico',
        'sello_externo_nombre' => 'Evaluador Asignado',
        'sello_externo_documento' => 'DNI del Evaluador',
        'sello_externo_expediente' => 'EXP-2026-CRÉD-0041',
        'sello_externo_estado' => 'CONFORME / APROBADO',
        'sello_externo_motivo' => 'Verificación y Aprobación Financiera / Sectorial',
    ];

    public const COLOR_PRESETS = [
        'emerald' => [
            'label' => 'Verde Esmeralda',
            'primary' => '#059669',
            'dark' => '#065f46',
            'soft' => '#f0fdf4',
            'border' => '#86efac',
            'ring' => '#10b981',
            'row_even' => '#f7fef9',
        ],
        'indigo' => [
            'label' => 'Índigo Ejecutivo',
            'primary' => '#4f46e5',
            'dark' => '#3730a3',
            'soft' => '#eef2ff',
            'border' => '#a5b4fc',
            'ring' => '#6366f1',
            'row_even' => '#f8faff',
        ],
        'amber' => [
            'label' => 'Ámbar Roble',
            'primary' => '#d97706',
            'dark' => '#92400e',
            'soft' => '#fffbeb',
            'border' => '#fcd34d',
            'ring' => '#f59e0b',
            'row_even' => '#fffdf5',
        ],
        'sky' => [
            'label' => 'Azul Océano',
            'primary' => '#0284c7',
            'dark' => '#0369a1',
            'soft' => '#f0f9ff',
            'border' => '#7dd3fc',
            'ring' => '#38bdf8',
            'row_even' => '#f8fcff',
        ],
        'rose' => [
            'label' => 'Carmesí Rubí',
            'primary' => '#e11d48',
            'dark' => '#9f1239',
            'soft' => '#fff1f2',
            'border' => '#fda4af',
            'ring' => '#f43f5e',
            'row_even' => '#fff8f8',
        ],
        'slate' => [
            'label' => 'Grafito Pizarra',
            'primary' => '#475569',
            'dark' => '#1e293b',
            'soft' => '#f8fafc',
            'border' => '#94a3b8',
            'ring' => '#64748b',
            'row_even' => '#fcfdfd',
        ],
        'teal' => [
            'label' => 'Verde Turquesa',
            'primary' => '#0d9488',
            'dark' => '#115e59',
            'soft' => '#f0fdfa',
            'border' => '#5eead4',
            'ring' => '#14b8a6',
            'row_even' => '#f7fdfc',
        ],
        'violet' => [
            'label' => 'Violeta Profundo',
            'primary' => '#7c3aed',
            'dark' => '#5b21b6',
            'soft' => '#f5f3ff',
            'border' => '#c4b5fd',
            'ring' => '#8b5cf6',
            'row_even' => '#faf8ff',
        ],
        'cyan' => [
            'label' => 'Cian Glaciar',
            'primary' => '#0891b2',
            'dark' => '#155e75',
            'soft' => '#ecfeff',
            'border' => '#67e8f9',
            'ring' => '#06b6d4',
            'row_even' => '#f6feff',
        ],
        'lime' => [
            'label' => 'Verde Lima',
            'primary' => '#65a30d',
            'dark' => '#3f6212',
            'soft' => '#f7fee7',
            'border' => '#bef264',
            'ring' => '#84cc16',
            'row_even' => '#fcfffa',
        ],
        'orange' => [
            'label' => 'Naranja Cálido',
            'primary' => '#ea580c',
            'dark' => '#9a3412',
            'soft' => '#fff7ed',
            'border' => '#fdba74',
            'ring' => '#f97316',
            'row_even' => '#fffcf8',
        ],
        'fuchsia' => [
            'label' => 'Fucsia Real',
            'primary' => '#c026d3',
            'dark' => '#86198f',
            'soft' => '#fdf4ff',
            'border' => '#f0abfc',
            'ring' => '#d946ef',
            'row_even' => '#fefaff',
        ],
        'stone' => [
            'label' => 'Piedra Arena',
            'primary' => '#78716c',
            'dark' => '#292524',
            'soft' => '#fafaf9',
            'border' => '#d6d3d1',
            'ring' => '#a8a29e',
            'row_even' => '#fdfdfc',
        ],
        'purple' => [
            'label' => 'Púrpura Imperial',
            'primary' => '#9333ea',
            'dark' => '#6b21a8',
            'soft' => '#faf5ff',
            'border' => '#d8b4fe',
            'ring' => '#a855f7',
            'row_even' => '#fcf9ff',
        ],
        'olive' => [
            'label' => 'Verde Oliva',
            'primary' => '#4d7c0f',
            'dark' => '#365314',
            'soft' => '#f4fce3',
            'border' => '#a3e635',
            'ring' => '#65a30d',
            'row_even' => '#f9fef2',
        ],
        'coffee' => [
            'label' => 'Miel Canela',
            'primary' => '#854d0e',
            'dark' => '#713f12',
            'soft' => '#fefce8',
            'border' => '#fef08a',
            'ring' => '#ca8a04',
            'row_even' => '#fffef5',
        ],
    ];

    public const OPACITY_PRESETS = [
        '0.02' => 'Muy Tenue (2%)',
        '0.04' => 'Estándar Recomendado (4%)',
        '0.07' => 'Visible (7%)',
        '0.10' => 'Marcado (10%)',
        '0.15' => 'Fuerte (15%)',
    ];

    private ?array $settings = null;

    private ?int $fundoId = null;

    public function __construct(private readonly ?CacheRepository $cache = null, ?int $fundoId = null)
    {
        $this->fundoId = $fundoId;
    }

    public function forFundo(?int $fundoId): self
    {
        $instance = new self($this->cache, $fundoId);

        return $instance;
    }

    private function resolveFundoId(): int
    {
        if ($this->fundoId !== null) {
            return $this->fundoId;
        }

        try {
            if (session()->has('fundo_id')) {
                return (int) session('fundo_id');
            }
            if (auth()->check() && auth()->user()->fundo_id) {
                return (int) auth()->user()->fundo_id;
            }
        } catch (Throwable) {
            // fallback
        }

        return 1;
    }

    public function settings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $fundoId = $this->resolveFundoId();

        $fetcher = function () use ($fundoId): array {
            try {
                $stored = ConfiguracionSistema::query()
                    ->where('fundo_id', $fundoId)
                    ->where('clave', 'like', 'pdf_%')
                    ->pluck('valor', 'clave')
                    ->mapWithKeys(fn ($v, $k) => [substr($k, 4) => $v])
                    ->toArray();

                return array_merge(self::DEFAULTS, $stored);
            } catch (Throwable) {
                return self::DEFAULTS;
            }
        };

        if ($this->cache) {
            $this->settings = $this->cache->remember("fundo.{$fundoId}.pdf_settings", now()->addMinutes(5), $fetcher);
        } else {
            $this->settings = $fetcher();
        }

        return $this->settings;
    }

    protected array $overrides = [];

    public function setOverride(string $key, $value): self
    {
        $this->overrides[$key] = $value;

        return $this;
    }

    public function setOverrides(array $overrides): self
    {
        $this->overrides = array_merge($this->overrides, $overrides);

        return $this;
    }

    public function clearOverrides(): self
    {
        $this->overrides = [];

        return $this;
    }

    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->overrides)) {
            return $this->overrides[$key];
        }

        $all = $this->settings();

        return $all[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public function showWatermark(): bool
    {
        return filter_var($this->get('mostrar_marca_agua'), FILTER_VALIDATE_BOOLEAN);
    }

    public function watermarkText(?string $fundoName = null): string
    {
        $custom = trim((string) $this->get('texto_marca_agua'));
        if ($custom !== '') {
            return $custom;
        }

        $name = $fundoName ?: (auth()->user()?->fundoActivo()?->nombre ?? 'AGROFUNDO');

        return mb_strtoupper($name, 'UTF-8').' • DOCUMENTO OFICIAL';
    }

    public function watermarkOpacity(): float
    {
        $val = (float) $this->get('opacidad_marca_agua', '0.04');

        return ($val > 0 && $val <= 0.3) ? $val : 0.04;
    }

    public function watermarkColor(): string
    {
        $color = (string) $this->get('color_marca_agua', '');

        return preg_match('/^#[a-fA-F0-9]{6}$/', $color) ? $color : $this->accentDark();
    }

    public function watermarkOrientation(): string
    {
        $orientation = (string) $this->get('orientacion_marca_agua', 'diagonal');

        return in_array($orientation, ['horizontal', 'recto'], true) ? 'horizontal' : 'diagonal';
    }

    public function watermarkRotation(): string
    {
        return $this->watermarkOrientation() === 'horizontal' ? '0deg' : '-24deg';
    }

    public function showHeaderLogo(): bool
    {
        return filter_var($this->get('mostrar_logo'), FILTER_VALIDATE_BOOLEAN);
    }

    public function colorPreset(): string
    {
        $style = (string) $this->get('estilo_color', 'emerald');

        return array_key_exists($style, self::COLOR_PRESETS) ? $style : 'emerald';
    }

    public function accentColor(): string
    {
        $style = $this->colorPreset();
        if ($style === 'custom') {
            $custom = (string) $this->get('color_acento', '');
            if (preg_match('/^#[a-fA-F0-9]{6}$/', $custom)) {
                return $custom;
            }
        }

        return self::COLOR_PRESETS[$style]['primary'] ?? '#047857';
    }

    public function accentDark(): string
    {
        $style = $this->colorPreset();

        return self::COLOR_PRESETS[$style]['dark'] ?? '#064e3b';
    }

    public function accentSoft(): string
    {
        $style = $this->colorPreset();

        return self::COLOR_PRESETS[$style]['soft'] ?? '#effaf2';
    }

    public function accentBorder(): string
    {
        $style = $this->colorPreset();

        return self::COLOR_PRESETS[$style]['border'] ?? '#cce5d3';
    }

    public function accentRowEven(): string
    {
        $style = $this->colorPreset();

        return self::COLOR_PRESETS[$style]['row_even'] ?? '#f0faf3';
    }

    public function accentRing(): string
    {
        $style = $this->colorPreset();

        return self::COLOR_PRESETS[$style]['ring'] ?? '#10b981';
    }

    public function modoColorTablas(): string
    {
        $val = (string) $this->get('modo_color_tablas', 'multi');

        return in_array($val, ['mono', 'monocromatico'], true) ? 'mono' : 'multi';
    }

    public function isMultiColorTablas(): bool
    {
        return $this->modoColorTablas() === 'multi';
    }

    public function paletaTablas(): array
    {
        $raw = $this->get('paleta_tablas', 'emerald,indigo,amber,sky,rose,slate,teal,violet');
        $keys = is_array($raw) ? $raw : explode(',', (string) $raw);
        $keys = array_filter(array_map('trim', $keys));

        return ! empty($keys) ? array_values($keys) : ['emerald', 'indigo', 'amber', 'sky', 'rose', 'slate', 'teal', 'violet'];
    }

    public function tableBorderRadiusPx(): int
    {
        $style = (string) $this->get('estilo_esquinas', 'redondeado');
        if ($style === 'clasico' || $style === 'recto') {
            return 0;
        }

        $radius = (int) $this->get('radio_esquinas', 5);
        return max(0, min($radius, 16));
    }

    public function isRoundedTables(): bool
    {
        $style = (string) $this->get('estilo_esquinas', 'redondeado');
        return ($style === 'redondeado' || $style === 'curvado') && $this->tableBorderRadiusPx() > 0;
    }

    public function tableBorderRadius(): string
    {
        return $this->isRoundedTables() ? ($this->tableBorderRadiusPx() . 'pt') : '0';
    }

    public function tableThemeForIndex(int $index): array
    {
        if (! $this->isMultiColorTablas()) {
            $preset = self::COLOR_PRESETS[$this->colorPreset()] ?? self::COLOR_PRESETS['emerald'];

            return [
                'key' => $this->colorPreset(),
                'label' => $preset['label'] ?? 'Institucional',
                'primary' => $this->accentColor(),
                'dark' => $this->accentDark(),
                'soft' => $this->accentSoft(),
                'border' => $this->accentBorder(),
                'ring' => $this->accentColor(),
                'row_even' => $preset['row_even'] ?? '#f8fafc',
                'badge_bg' => $this->accentSoft(),
                'badge_text' => $this->accentDark(),
            ];
        }

        $palettes = $this->paletaTablas();
        $paletteKey = $palettes[$index % count($palettes)] ?? 'emerald';
        $preset = self::COLOR_PRESETS[$paletteKey] ?? self::COLOR_PRESETS['emerald'];

        return [
            'key' => $paletteKey,
            'label' => $preset['label'],
            'primary' => $preset['primary'],
            'dark' => $preset['dark'],
            'soft' => $preset['soft'],
            'border' => $preset['border'],
            'ring' => $preset['ring'],
            'row_even' => $preset['row_even'] ?? '#f8fafc',
            'badge_bg' => $preset['soft'],
            'badge_text' => $preset['dark'],
        ];
    }

    public function showFooter(): bool
    {
        return filter_var($this->get('mostrar_pie'), FILTER_VALIDATE_BOOLEAN);
    }

    public function footerText(?string $fundoName = null, ?string $brandName = null): string
    {
        $custom = trim((string) $this->get('texto_pie'));
        if ($custom !== '') {
            return $custom;
        }

        $brand = $brandName ?: 'AGROFUNDO';

        return "Documento emitido por {$brand}. Información oficial y certificada del fundo activo.";
    }

    public function showPageNumbers(): bool
    {
        return filter_var($this->get('mostrar_num_pagina'), FILTER_VALIDATE_BOOLEAN);
    }

    public function showGeneratedDateTime(): bool
    {
        return filter_var($this->get('mostrar_fecha_hora'), FILTER_VALIDATE_BOOLEAN);
    }

    public function showGeneratedBy(): bool
    {
        return filter_var($this->get('mostrar_generado_por'), FILTER_VALIDATE_BOOLEAN);
    }

    public function showSignatures(): bool
    {
        return filter_var($this->get('mostrar_firmas'), FILTER_VALIDATE_BOOLEAN);
    }

    public function showSignature1(): bool
    {
        return $this->showSignatures() && filter_var($this->get('mostrar_firma_1', 'true'), FILTER_VALIDATE_BOOLEAN);
    }

    public function showSignature2(): bool
    {
        return $this->showSignatures() && filter_var($this->get('mostrar_firma_2', 'true'), FILTER_VALIDATE_BOOLEAN);
    }

    public function activeSignaturesCount(): int
    {
        if (! $this->showSignatures()) {
            return 0;
        }

        $count = 0;
        if ($this->showSignature1()) {
            $count++;
        }
        if ($this->showSignature2()) {
            $count++;
        }
        if ($this->showExternalStamp()) {
            $count++;
        }

        return $count;
    }

    public function signatureType(): string
    {
        $type = (string) $this->get('tipo_firma', 'digital');

        return in_array($type, ['digital', 'clasica', 'ambas'], true) ? $type : 'digital';
    }

    public function signature1Cargo(): string
    {
        return (string) $this->get('firma_1_cargo', 'Responsable de Fundo / Administración');
    }

    public function signature1Nombre(): string
    {
        return (string) $this->get('firma_1_nombre', 'Firma Digital Autorizada');
    }

    public function signature1Documento(): string
    {
        return (string) $this->get('firma_1_documento', 'DNI / RUC Autorizado');
    }

    public function signature2Cargo(): string
    {
        return (string) $this->get('firma_2_cargo', 'Control Técnico / Médico Veterinario');
    }

    public function signature2Nombre(): string
    {
        return (string) $this->get('firma_2_nombre', 'Supervisión y Conformidad');
    }

    public function signature2Documento(): string
    {
        return (string) $this->get('firma_2_documento', 'Reg. Profesional / Colegiatura');
    }

    public function signatureMotivo(): string
    {
        return (string) $this->get('firma_motivo', 'Conformidad y Validación Técnica Oficial');
    }

    public function signatureSoftware(): string
    {
        return (string) $this->get('firma_software', 'AGROFUNDO ERP v2.6 · Software Ganadero');
    }

    public function showSignatureHash(): bool
    {
        return filter_var($this->get('firma_mostrar_hash'), FILTER_VALIDATE_BOOLEAN);
    }

    public function showVerificationSeal(): bool
    {
        return filter_var($this->get('mostrar_sello_autenticidad'), FILTER_VALIDATE_BOOLEAN);
    }

    public function showExternalStamp(): bool
    {
        return filter_var($this->get('mostrar_sello_externo'), FILTER_VALIDATE_BOOLEAN);
    }

    public function externalStampEntidad(): string
    {
        return (string) $this->get('sello_externo_entidad', 'Entidad Financiera / Bancaria');
    }

    public function externalStampCargo(): string
    {
        return (string) $this->get('sello_externo_cargo', 'Analista de Crédito / Auditor Técnico');
    }

    public function externalStampNombre(): string
    {
        return (string) $this->get('sello_externo_nombre', 'Evaluador / Analista Asignado');
    }

    public function externalStampDocumento(): string
    {
        return (string) $this->get('sello_externo_documento', 'Matrícula / Registro N°');
    }

    public function externalStampExpediente(): string
    {
        return (string) $this->get('sello_externo_expediente', 'Exp. Evaluación / Crédito');
    }

    public function externalStampEstado(): string
    {
        return (string) $this->get('sello_externo_estado', 'CONFORME / APROBADO');
    }

    public function externalStampMotivo(): string
    {
        return (string) $this->get('sello_externo_motivo', 'Verificación y Aprobación Financiera / Sectorial');
    }

    public function signatureScale(): int
    {
        $scale = (int) $this->get('escala_firmas', 100);

        return max(35, min($scale, 170));
    }

    public function signatureBodyFontSizePt(): string
    {
        $factor = $this->signatureScale() / 100;

        return round(3.8 * $factor, 2) . 'pt';
    }

    public function signatureTitleFontSizePt(): string
    {
        $factor = $this->signatureScale() / 100;

        return round(4.8 * $factor, 2) . 'pt';
    }

    public function signatureLabelFontSizePt(): string
    {
        $factor = $this->signatureScale() / 100;

        return round(3.8 * $factor, 2) . 'pt';
    }

    public function signatureMaxWidthPt(): string
    {
        $factor = $this->signatureScale() / 100;

        return round(170 * $factor, 1) . 'pt';
    }

    public static function saveConfig(int $fundoId, array $data, ?CacheRepository $cache = null): void
    {
        foreach ($data as $key => $value) {
            $storageKey = str_starts_with($key, 'pdf_') ? $key : "pdf_{$key}";
            $val = is_bool($value)
                ? ($value ? 'true' : 'false')
                : (is_array($value) ? implode(',', $value) : (string) $value);

            ConfiguracionSistema::query()->updateOrCreate(
                ['fundo_id' => $fundoId, 'clave' => $storageKey],
                ['valor' => $val]
            );
        }

        if ($cache) {
            $cache->forget("fundo.{$fundoId}.pdf_settings");
        }
    }

    public static function resetDefaults(int $fundoId, ?CacheRepository $cache = null): void
    {
        ConfiguracionSistema::query()
            ->where('fundo_id', $fundoId)
            ->where('clave', 'like', 'pdf_%')
            ->delete();

        if ($cache) {
            $cache->forget("fundo.{$fundoId}.pdf_settings");
        }
    }
}
