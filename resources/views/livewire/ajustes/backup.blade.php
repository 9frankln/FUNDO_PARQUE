@php
    $formatBackupSize = static function (?int $bytes): string {
        if (!$bytes) return '0 KB';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2).' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2).' MB';
        return number_format($bytes / 1024, 1).' KB';
    };
    $backupTypeLabel = static fn (?string $type): string => match ($type) {
        'files' => 'Fotos y archivos',
        'complete' => 'Todo el fundo',
        default => 'Datos del fundo',
    };
    $backupTriggerLabel = static fn (?string $trigger): string => match ($trigger) {
        'scheduled' => 'Programado',
        'uploaded' => 'Importado',
        'pre_restore' => 'Previo a restauración',
        default => 'Manual',
    };
    $shortUserName = static function (?string $name): string {
        if (! $name) return 'Sistema';
        $parts = collect(preg_split('/\s+/', trim($name)))->filter()->values();
        if ($parts->isEmpty()) return 'Sistema';
        $first = ucfirst(mb_strtolower((string) $parts->get(0)));
        $last = $parts->get(1) ? ucfirst(mb_strtolower((string) $parts->get(1))) : '';
        return trim($first.' '.$last);
    };
@endphp

<section class="space-y-6">
    {{-- Header principal --}}
    <div class="agro-card relative overflow-hidden p-5 sm:p-6">
        <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-white shadow-lg shadow-emerald-800/20 dark:bg-emerald-400 dark:text-emerald-950">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 5v5h10V5M8 15h8"/></svg>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="agro-kicker">Continuidad de datos</p>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold {{ $backupSettings['enabled'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                            {{ $backupSettings['enabled'] ? 'AUTOMÁTICO ACTIVO' : 'AUTOMÁTICO PAUSADO' }}
                        </span>
                    </div>
                    <h2 class="mt-1 text-xl font-extrabold sm:text-2xl">Centro de respaldos</h2>
                    <p class="mt-1 max-w-2xl text-xs leading-5 text-zinc-500 sm:text-sm">Respaldos ZIP cifrados del fundo y Gestión web, con contenido y frecuencia configurables.</p>
                </div>
            </div>
            @if(auth()->user()->tienePermiso('ajustes', 'exportar'))
                <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                    <button wire:click="generateBackup" wire:loading.attr="disabled" wire:target="generateBackup" class="agro-button w-full lg:w-auto">
                        <span wire:loading.remove wire:target="generateBackup" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0-4-4m4 4 4-4M5 20h14"/></svg>
                            Generar {{ $backupTypeLabel($backupScope) }}
                        </span>
                        <span wire:loading wire:target="generateBackup">Generando ZIP...</span>
                    </button>
                    @if($canManageFundoAdmins)
                    <button type="button" x-on:click.prevent="confirmDelete('¿Liberar bloqueo de emergencia?', 'Si una operación previa quedó colgada, esto restablece la posibilidad de generar o restaurar backups.').then((res) => { if (res.isConfirmed) $wire.forceReleaseBackupLock() })" class="agro-button-secondary w-full lg:w-auto text-xs py-2 text-rose-600 dark:text-rose-400" title="Liberar bloqueo en caso de atasco">
                        Liberar bloqueo
                    </button>
                    @endif
                </div>
            @endif
        </div>
        <div wire:loading wire:target="generateBackup,uploadBackup,restoreBackup" class="mt-5 rounded-2xl border border-emerald-500/25 bg-emerald-50 p-3 dark:border-emerald-400/20 dark:bg-emerald-400/10">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 animate-spin text-emerald-700 dark:text-emerald-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"/></svg>
                <div class="min-w-0 flex-1">
                    <div class="h-1.5 overflow-hidden rounded-full bg-emerald-200 dark:bg-emerald-950"><div class="h-full w-2/3 animate-pulse rounded-full bg-emerald-600 dark:bg-emerald-400"></div></div>
                    <p class="mt-1.5 text-[10px] font-semibold text-emerald-800 dark:text-emerald-200">Procesando y verificando integridad. No cierres pantalla.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Métricas superiores con jerarquía visual y paleta viva --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        {{-- 1. Último Backup --}}
        <article class="col-span-2 rounded-2xl border border-emerald-500/25 bg-emerald-50/50 p-4 transition dark:border-emerald-500/20 dark:bg-emerald-950/20 sm:col-span-1 shadow-2xs">
            <span class="text-[9.5px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Último backup</span>
            <strong class="mt-1.5 block text-sm font-black text-zinc-900 dark:text-zinc-100">{{ $backupOverview['last']?->created_at?->format('d/m/Y H:i') ?? 'Sin copias' }}</strong>
            <small class="mt-0.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">{{ $backupOverview['last'] ? $backupTypeLabel($backupOverview['last']->type) : 'Genera primera copia' }}</small>
        </article>

        {{-- 2. Próximo --}}
        <article class="rounded-2xl border border-indigo-500/25 bg-indigo-50/50 p-4 transition dark:border-indigo-500/20 dark:bg-indigo-950/20 shadow-2xs">
            <span class="text-[9.5px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Próximo programado</span>
            <strong class="mt-1.5 block text-sm font-black text-zinc-900 dark:text-zinc-100">{{ $backupOverview['next']?->format('d/m H:i') ?? 'No programado' }}</strong>
            <small class="mt-0.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">{{ $backupTypeLabel($backupSettings['scope']) }}</small>
        </article>

        {{-- 3. Espacio usado --}}
        <article class="rounded-2xl border border-sky-500/25 bg-sky-50/50 p-4 transition dark:border-sky-500/20 dark:bg-sky-950/20 shadow-2xs">
            <span class="text-[9.5px] font-black uppercase tracking-wider text-sky-700 dark:text-sky-400">Espacio en disco</span>
            <strong class="mt-1.5 block text-sm font-black text-zinc-900 dark:text-zinc-100">{{ $formatBackupSize($backupOverview['size_bytes'] ?? 0) }}</strong>
            <small class="mt-0.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Servidor seguro</small>
        </article>

        {{-- 4. Copias --}}
        <article class="rounded-2xl border border-purple-500/25 bg-purple-50/50 p-4 transition dark:border-purple-500/20 dark:bg-purple-950/20 shadow-2xs">
            <span class="text-[9.5px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400">Total de copias</span>
            <strong class="mt-1.5 block text-sm font-black text-zinc-900 dark:text-zinc-100">{{ $backupOverview['count'] ?? 0 }}</strong>
            <small class="mt-0.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">{{ $backupSettings['retention_count'] }} máx. por tipo</small>
        </article>

        {{-- 5. Último error --}}
        <article class="rounded-2xl border p-4 transition shadow-2xs {{ ($backupOverview['last_error'] ?? null) ? 'border-rose-500/30 bg-rose-50/50 dark:border-rose-500/20 dark:bg-rose-950/20' : 'border-emerald-500/25 bg-emerald-50/50 dark:border-emerald-500/20 dark:bg-emerald-950/20' }}">
            <span class="text-[9.5px] font-black uppercase tracking-wider {{ ($backupOverview['last_error'] ?? null) ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">Estado / Error</span>
            <strong class="mt-1.5 block text-sm font-black {{ ($backupOverview['last_error'] ?? null) ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ ($backupOverview['last_error'] ?? null) ? $backupOverview['last_error']->failed_at?->format('d/m H:i') : 'Sin errores' }}</strong>
            <small class="mt-0.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">{{ ($backupOverview['last_error'] ?? null) ? 'Revisar detalles' : 'Sistema 100% operativo' }}</small>
        </article>
    </div>

    {{-- Panel central: 2 columnas en desktop --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Columna Izquierda: Programación automática --}}
        <aside class="space-y-5 lg:col-span-4 xl:col-span-4">
            <form wire:submit="saveBackupSettings" class="agro-card overflow-hidden">
                <header class="border-b border-zinc-200 p-4 dark:border-zinc-800">
                    <p class="agro-kicker">Programación</p>
                    <h3 class="mt-1 text-lg font-extrabold">Respaldo automático</h3>
                    <p class="mt-1 text-xs text-zinc-500">Elige cuándo ejecutar y qué contenido proteger.</p>
                </header>
                <div class="space-y-4 p-4">
                    <label class="flex items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-400/20 dark:bg-emerald-400/10">
                        <span><strong class="block text-xs">Generación automática</strong><small class="text-zinc-500">Scheduler cada 15 minutos</small></span>
                        <input type="checkbox" wire:model="backupSettings.enabled" class="agro-checkbox h-5 w-5 rounded shrink-0">
                    </label>
                    <fieldset>
                        <legend class="mb-2 text-xs font-bold">Frecuencia</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['custom' => 'Personalizada', 'daily' => 'Diaria', 'weekly' => 'Semanal', 'monthly' => 'Mensual'] as $schedule => $label)
                                <label class="cursor-pointer min-w-0">
                                    <input type="radio" wire:model.live="backupSettings.schedule" value="{{ $schedule }}" class="peer sr-only">
                                    <span class="flex min-h-10 items-center justify-center rounded-xl border border-zinc-200 px-1.5 text-center text-[10px] sm:text-xs font-bold peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 dark:border-zinc-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/10 dark:peer-checked:text-emerald-200 truncate">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    @if($backupSettings['schedule'] === 'custom')
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <span class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Ejecutar cada</span>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input type="number" min="1" max="{{ ($backupSettings['interval_unit'] ?? 'hours') === 'days' ? 30 : 168 }}" wire:model="backupSettings.interval_value" class="w-full sm:w-24 rounded-xl border border-zinc-300 px-3 py-2 text-xs dark:border-zinc-700 bg-white dark:bg-zinc-900">
                                <div class="w-full sm:w-32">
                                    <x-filter-select model="backupSettings.interval_unit" :options="['hours' => 'Horas', 'days' => 'Días']" tone="emerald" live compact />
                                </div>
                            </div>
                            @error('backupSettings.interval_value')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                            @error('backupSettings.interval_unit')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                        </div>
                    @endif
                    <fieldset>
                        <legend class="mb-2 text-xs font-bold">Contenido automático</legend>
                        <div class="space-y-2">
                            @foreach(['database' => 'Datos del fundo', 'files' => 'Fotos y archivos', 'complete' => 'Todo el fundo'] as $type => $label)
                                <label class="block cursor-pointer">
                                    <input type="radio" wire:model="backupSettings.scope" value="{{ $type }}" class="peer sr-only">
                                    <span class="flex items-center justify-between rounded-xl border border-zinc-200 px-3 py-2.5 text-xs font-bold peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 dark:border-zinc-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/10 dark:peer-checked:text-emerald-200">
                                        <span>{{ $label }}</span>
                                        <span class="text-[10px] opacity-60">{{ $type === 'complete' ? 'DATOS + ARCHIVOS' : ($type === 'files' ? 'SOLO ARCHIVOS' : 'SOLO DATOS') }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="mb-2 text-xs font-bold">Componentes adicionales</legend>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                                <input type="checkbox" wire:model="backupSettings.include_web" class="agro-checkbox mt-0.5 h-4 w-4 rounded">
                                <span><strong class="block text-xs">Gestión web</strong><small class="text-[10px] leading-4 text-zinc-500">Textos, identidad, medios y encuadres.</small></span>
                            </label>
                        </div>
                    </fieldset>
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold">Copias por tipo</span>
                        <input type="number" min="2" max="100" wire:model="backupSettings.retention_count" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs dark:border-zinc-700">
                        @error('backupSettings.retention_count')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </label>
                    @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))
                        <button class="agro-button w-full" type="submit">Guardar programación</button>
                    @endif
                    <p class="rounded-xl bg-zinc-50 p-3 text-[10px] leading-4 text-zinc-500 dark:bg-zinc-900">El servidor revisa la programación cada 15 minutos.</p>
                </div>
            </form>
        </aside>

        {{-- Columna Derecha: Tipo de copia, Subir backup compacto, Info Seguridad --}}
        <div class="min-w-0 space-y-5 lg:col-span-8 xl:col-span-8">
            {{-- Selección de Contenido --}}
            <div class="agro-card p-5">
                <div class="mb-3"><p class="agro-kicker">Tipo de copia</p><h3 class="mt-1 text-lg font-extrabold">Selecciona contenido para copia manual</h3></div>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach([
                        'database' => ['Datos del fundo', 'Registros operativos y configuración local.', 'Cada 6 horas'],
                        'files' => ['Fotos y archivos', 'Fotografías, evidencias y comprobantes.', 'Diario'],
                        'complete' => ['Todo el fundo', 'Datos y todos los archivos disponibles.', 'Semanal'],
                    ] as $type => [$label, $description, $recommendation])
                        <button type="button" wire:click="$set('backupScope', '{{ $type }}')" class="relative rounded-2xl border-2 p-4 text-left transition {{ $backupScope === $type ? 'border-emerald-600 bg-emerald-50 shadow-sm dark:border-emerald-400 dark:bg-emerald-400/10' : 'border-zinc-200 bg-white hover:border-emerald-300 dark:border-zinc-700 dark:bg-zinc-900' }}">
                            @if($backupScope === $type)<span class="absolute right-3 top-3 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-700 text-[10px] text-white dark:bg-emerald-400 dark:text-emerald-950">✓</span>@endif
                            <strong class="block pr-6 text-sm">{{ $label }}</strong><p class="mt-1 text-[11px] leading-4 text-zinc-500">{{ $description }}</p><span class="mt-3 inline-flex rounded-full bg-zinc-100 px-2 py-1 text-[8px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">RECOMENDADO: {{ strtoupper($recommendation) }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-3.5 transition {{ $backupIncludeWeb ? 'border-emerald-600 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-400/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}"><input type="checkbox" wire:model.live="backupIncludeWeb" class="agro-checkbox mt-0.5 h-4 w-4 rounded"><span><strong class="block text-xs">Incluir Gestión web</strong><small class="mt-0.5 block text-[10px] leading-4 text-zinc-500">Secciones públicas, identidad, encuadres y archivos web.</small></span></label>
                </div>

                {{-- Contenido en vivo del fundo (coherente con la Zona de peligro) --}}
                @php $liveContent = $this->dangerZoneCounts(); @endphp
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-3.5 dark:border-emerald-400/20 dark:bg-emerald-400/5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[11px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Contenido actual a respaldar</p>
                        <span class="shrink-0 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-black tabular-nums text-white">{{ number_format($liveContent['records']) }} reg. · {{ number_format($liveContent['files']) }} arch.</span>
                    </div>
                    <ul class="mt-2 grid gap-1.5 text-[11px] leading-relaxed text-zinc-700 dark:text-zinc-300 sm:grid-cols-2">
                        @foreach($liveContent['groups'] as $group)
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-white/70 px-2 py-1 dark:bg-zinc-950/40">
                                <span class="truncate">• {{ $group['label'] }}</span>
                                <span class="shrink-0 font-black tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format($group['total']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-[10px] leading-4 text-zinc-500">Conteo en vivo del fundo activo. Coincide con lo que se eliminaría en la Zona de peligro.</p>
                </div>
            </div>

            {{-- Importar / Subir Backup (COMPACTO Y BIEN ESTRUCTURADO) --}}
            @if(auth()->user()->tienePermiso('ajustes', 'restaurar'))
                <form wire:submit="uploadBackup" class="agro-card p-5" x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true; progress = 0" x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
                        <div>
                            <p class="agro-kicker">Importar</p>
                            <h3 class="mt-0.5 text-lg font-extrabold">Subir backup</h3>
                            <p class="text-xs text-zinc-500">Acepta ZIP firmado por esta instalación y perteneciente al fundo activo.</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">ZIP Estructurado</span>
                    </div>

                    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                        <label class="flex flex-1 cursor-pointer items-center justify-between gap-3 rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 px-4 py-3 transition hover:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="h-6 w-6 shrink-0 text-emerald-700 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L8 8m4-4 4 4M5 14v5h14v-5"/></svg>
                                <div class="min-w-0">
                                    <strong class="block text-xs font-bold truncate">Seleccionar archivo .zip</strong>
                                    <small class="block text-[10px] text-zinc-500 truncate">{{ $backupUpload?->getClientOriginalName() ?? 'Máximo 10 GB (Sin límite de velocidad)' }}</small>
                                </div>
                            </div>
                            <span class="rounded-xl bg-white px-3 py-1.5 text-[11px] font-bold shadow-sm dark:bg-zinc-800 shrink-0">Examinar</span>
                            <input type="file" wire:model="backupUpload" accept=".zip,application/zip" class="sr-only">
                        </label>

                        <button type="submit" wire:loading.attr="disabled" wire:target="backupUpload,uploadBackup" class="agro-button-secondary h-12 w-full sm:w-auto px-6 whitespace-nowrap shrink-0">
                            <span wire:loading.remove wire:target="uploadBackup">Validar e importar</span>
                            <span wire:loading wire:target="uploadBackup">Importando...</span>
                        </button>
                    </div>

                    @error('backupUpload')
                        <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p>
                    @enderror

                    @if($uploadLimitWarning)
                        <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[10px] leading-4 text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">Límite del servidor: <strong>{{ $uploadServerLimit }}</strong>. PHP limita subidas por debajo del máximo declarado de {{ $maxUploadLimit }}; archivos mayores fallarán a nivel de servidor.</p>
                    @endif

                    <div x-cloak x-show="uploading" class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-400/20 dark:bg-emerald-400/10">
                        <div class="flex items-center justify-between gap-3 text-[10px] font-bold text-emerald-800 dark:text-emerald-200">
                            <span>Subiendo al servidor...</span>
                            <span x-text="`${progress}%`"></span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-emerald-200 dark:bg-emerald-950">
                            <div class="h-full rounded-full bg-emerald-600 transition-[width] duration-150 dark:bg-emerald-400" x-bind:style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </form>
            @endif

            {{-- Cards de Seguridad --}}
            <div class="grid gap-3 sm:grid-cols-2">
                <article class="agro-card p-4">
                    <p class="agro-kicker">Seguridad real</p>
                    <h3 class="mt-1 text-base font-extrabold">ZIP protegido</h3>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-[10px] font-bold">
                        <span class="rounded-lg bg-emerald-50 p-2 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">AES-256</span>
                        <span class="rounded-lg bg-emerald-50 p-2 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">SHA-256</span>
                        <span class="rounded-lg bg-zinc-50 p-2 text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">Firma HMAC</span>
                        <span class="rounded-lg bg-zinc-50 p-2 text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">Aislamiento por fundo</span>
                    </div>
                </article>
                <article class="agro-card p-4">
                    <p class="agro-kicker">Restauración segura</p>
                    <h3 class="mt-1 text-base font-extrabold">Recuperación con retorno</h3>
                    <p class="mt-2 text-[11px] leading-5 text-zinc-500">Antes de restaurar se genera backup automático del estado actual. SQL subido nunca se ejecuta; restauración usa datos estructurados allowlist.</p>
                </article>
            </div>
        </div>
    </div>

    {{-- Historial de copias (PARTE FINAL, AMPLIO Y BIEN ORGANIZADO) --}}
    <div class="agro-card p-5 space-y-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="agro-kicker">Continuidad</p>
                <h3 class="mt-0.5 text-xl font-extrabold">Historial de copias</h3>
                <p class="text-xs text-zinc-500">Descarga, verifica, restaura o elimina cada backup registrado.</p>
            </div>
            <div class="text-xs font-bold text-zinc-500">
                Total: {{ $backups->total() }} respaldos
            </div>
        </div>

        <div class="agro-record-surface overflow-hidden rounded-2xl border">
            <div class="hidden overflow-x-auto lg:block">
                <table class="w-full text-left">
                    <thead class="agro-record-header text-[10px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="p-3.5 whitespace-nowrap">Fecha</th>
                            <th class="p-3.5">Contenido</th>
                            <th class="p-3.5 whitespace-nowrap">Tamaño</th>
                            <th class="p-3.5">Restaurado</th>
                            <th class="p-3.5">Duración</th>
                            <th class="p-3.5">Estado</th>
                            <th class="p-3.5">Integridad</th>
                            <th class="p-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="agro-record-list text-sm">
                        @forelse($backups as $backup)
                            @php
                                $seconds = $backup->started_at && $backup->completed_at ? $backup->started_at->diffInSeconds($backup->completed_at) : null;
                                $components = $backup->components ?? [];
                                $restoresCount = (int) ($backup->restores_count ?? 0);
                            @endphp
                            <tr wire:key="backup-{{ $backup->id }}">
                                <td class="p-3.5 whitespace-nowrap"><strong class="block text-zinc-900 dark:text-zinc-100">{{ $backup->created_at->format('d/m/Y') }}</strong><span class="text-xs text-zinc-500">{{ $backup->created_at->format('H:i:s') }}</span></td>
                                <td class="p-3.5"><strong class="block whitespace-nowrap text-xs text-zinc-900 dark:text-zinc-100">{{ $backupTypeLabel($backup->type) }}</strong><span class="mt-0.5 block text-[10px] text-zinc-500">{{ $backupTriggerLabel($backup->trigger) }} · {{ strtoupper($backup->format ?? 'sql') }}</span><span class="mt-0.5 block truncate text-[11px] font-semibold text-zinc-700 dark:text-zinc-200" title="{{ $backup->requester?->name ?? 'Sistema' }}">{{ $shortUserName($backup->requester?->name) }}</span>@if($components)<span class="mt-1 flex flex-wrap gap-1">@if($components['web'] ?? false)<span class="rounded bg-sky-100 px-1.5 py-0.5 text-[8px] font-bold text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">WEB</span>@endif</span>@endif</td>
                                <td class="p-3.5 whitespace-nowrap"><strong class="block text-xs text-zinc-900 dark:text-zinc-100">{{ $formatBackupSize($backup->size_bytes) }}</strong><span class="block text-[10px] text-zinc-500">{{ number_format($backup->record_count ?? 0) }} reg. · {{ number_format($backup->photo_count ?? 0) }} arch.</span></td>
                                <td class="p-3.5"><span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2 py-1 text-[10px] font-bold {{ $restoresCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }}">{{ $restoresCount > 0 ? $restoresCount.'×' : '—' }}</span></td>
                                <td class="p-3.5 whitespace-nowrap text-xs">{{ $seconds !== null ? ($seconds < 60 ? $seconds.' s' : floor($seconds / 60).' min') : '—' }}</td>
                                <td class="p-3.5"><x-status-badge :value="$backup->status" :label="match($backup->status) { 'completed' => 'Completado', 'failed' => 'Fallido', 'running' => 'Generando', default => 'Pendiente' }" :tone="match($backup->status) { 'completed' => 'emerald', 'failed' => 'rose', 'running' => 'amber', default => 'slate' }" /></td>
                                <td class="p-3.5"><span class="inline-flex items-center gap-1 whitespace-nowrap text-[10px] font-bold {{ $backup->integrity_verified_at ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-500' }}"><span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $backup->integrity_verified_at ? 'bg-emerald-500' : ($backup->checksum_sha256 ? 'bg-amber-400' : 'bg-zinc-400') }}"></span>{{ $backup->integrity_verified_at ? 'Verificado' : ($backup->checksum_sha256 ? 'SHA' : 'Pendiente') }}</span></td>
                                <td class="p-3.5"><div class="flex justify-end gap-1"><x-table-action type="view" wire:click="openBackupDetails({{ $backup->id }})" label="Ver detalles" />@if($backup->status === 'completed' && $backup->path && auth()->user()->tienePermiso('ajustes', 'exportar'))<x-table-action type="verify" wire:click="verifyBackup({{ $backup->id }})" label="Verificar integridad SHA-256" /><x-table-action type="download" href="{{ route('ajustes.backups.download', $backup) }}" label="Descargar {{ strtoupper($backup->format ?? 'SQL') }}" />@endif @if($backup->status === 'completed' && $backup->format === 'zip' && auth()->user()->tienePermiso('ajustes', 'restaurar'))<x-table-action type="restore" wire:click="openRestoreModal({{ $backup->id }})" label="Restaurar backup" />@endif @if(auth()->user()->tienePermiso('ajustes', 'eliminar'))<x-table-action type="delete" x-on:click.prevent="confirmDelete('¿Eliminar este backup?', 'Archivo e historial se eliminarán permanentemente.').then((res) => { if (res.isConfirmed) $wire.deleteBackup({{ $backup->id }}) })" label="Eliminar backup" />@endif</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-10 text-center text-sm text-zinc-500">Sin backups almacenados. Selecciona tipo y genera primera copia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="divide-y divide-zinc-200 dark:divide-zinc-800 lg:hidden">
                @forelse($backups as $backup)
                    @php
                        $seconds = $backup->started_at && $backup->completed_at ? $backup->started_at->diffInSeconds($backup->completed_at) : null;
                        $components = $backup->components ?? [];
                    @endphp
                    <article wire:key="backup-mobile-{{ $backup->id }}" class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3"><div><strong class="block text-sm">{{ $backupTypeLabel($backup->type) }}</strong><span class="text-xs text-zinc-500">{{ $backup->created_at->format('d/m/Y · H:i') }} · {{ $backupTriggerLabel($backup->trigger) }}</span></div><x-status-badge :value="$backup->status" :label="match($backup->status) { 'completed' => 'Completado', 'failed' => 'Fallido', 'running' => 'Generando', default => 'Pendiente' }" :tone="match($backup->status) { 'completed' => 'emerald', 'failed' => 'rose', 'running' => 'amber', default => 'slate' }" /></div>
                        @if($components)<div class="flex flex-wrap gap-1">@if($components['web'] ?? false)<span class="rounded bg-sky-100 px-2 py-1 text-[9px] font-bold text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">Gestión web</span>@endif</div>@endif
                        <div class="grid grid-cols-4 gap-2 rounded-xl bg-zinc-50 p-3 text-center dark:bg-zinc-900"><span><small class="block text-[9px] uppercase text-zinc-500">Tamaño</small><strong class="text-[11px]">{{ $formatBackupSize($backup->size_bytes) }}</strong></span><span><small class="block text-[9px] uppercase text-zinc-500">Contenido</small><strong class="text-[11px]">{{ number_format(($backup->record_count ?? 0) + ($backup->photo_count ?? 0)) }}</strong></span><span><small class="block text-[9px] uppercase text-zinc-500">Restaurado</small><strong class="text-[11px]">{{ (int) ($backup->restores_count ?? 0) > 0 ? (int) $backup->restores_count.'×' : '—' }}</strong></span><span><small class="block text-[9px] uppercase text-zinc-500">Integridad</small><strong class="text-[11px]">{{ $backup->integrity_verified_at ? 'Verificada' : ($backup->checksum_sha256 ? 'Registrada' : '—') }}</strong></span></div>
                        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                            <x-table-action type="view" wire:click="openBackupDetails({{ $backup->id }})" label="Ver detalles del backup" class="!h-10 !w-10" />
                            @if($backup->status === 'completed' && $backup->path && auth()->user()->tienePermiso('ajustes', 'exportar'))
                                <x-table-action type="verify" wire:click="verifyBackup({{ $backup->id }})" label="Verificar integridad SHA-256" class="!h-10 !w-10" />
                                <x-table-action type="download" href="{{ route('ajustes.backups.download', $backup) }}" label="Descargar {{ strtoupper($backup->format ?? 'SQL') }}" class="!h-10 !w-10" />
                            @endif
                            @if($backup->status === 'completed' && $backup->format === 'zip' && auth()->user()->tienePermiso('ajustes', 'restaurar'))
                                <x-table-action type="restore" wire:click="openRestoreModal({{ $backup->id }})" label="Restaurar backup" class="!h-10 !w-10" />
                            @endif
                            @if(auth()->user()->tienePermiso('ajustes', 'eliminar'))
                                <x-table-action type="delete" x-on:click.prevent="confirmDelete('¿Eliminar este backup?', 'Archivo e historial se eliminarán permanentemente.').then((res) => { if (res.isConfirmed) $wire.deleteBackup({{ $backup->id }}) })" label="Eliminar backup" class="!h-10 !w-10" />
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center text-sm text-zinc-500">Sin backups almacenados.</div>
                @endforelse
            </div>
        </div>
        <div class="agro-table-footer">
            <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="backupsPerPage" :options="$perPageOptions" tone="emerald" live compact /></div>
            <div class="min-w-0">{{ $backups->links('components.pagination') }}</div>
        </div>
    </div>
</section>

{{-- Modales --}}
@if($showBackupDetails && $viewingBackup)
    @php
        $detailSeconds = $viewingBackup->started_at && $viewingBackup->completed_at ? $viewingBackup->started_at->diffInSeconds($viewingBackup->completed_at) : null;
        $detailComponents = $viewingBackup->components ?? [];
        $statusTone = match($viewingBackup->status) {
            'completed' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/40', 'border' => 'border-emerald-200 dark:border-emerald-800/60', 'text' => 'text-emerald-800 dark:text-emerald-300', 'badge' => 'bg-emerald-600 text-white'],
            'failed' => ['bg' => 'bg-rose-50 dark:bg-rose-950/40', 'border' => 'border-rose-200 dark:border-rose-800/60', 'text' => 'text-rose-800 dark:text-rose-300', 'badge' => 'bg-rose-600 text-white'],
            'running' => ['bg' => 'bg-amber-50 dark:bg-amber-950/40', 'border' => 'border-amber-200 dark:border-amber-800/60', 'text' => 'text-amber-800 dark:text-amber-300', 'badge' => 'bg-amber-600 text-white'],
            default => ['bg' => 'bg-zinc-50 dark:bg-zinc-900/40', 'border' => 'border-zinc-200 dark:border-zinc-800', 'text' => 'text-zinc-800 dark:text-zinc-300', 'badge' => 'bg-zinc-600 text-white'],
        };
    @endphp
    <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay" @click.self="closeBackupDetails">
        <section role="dialog" aria-modal="true" aria-label="Detalles del backup" class="agro-dialog agro-dialog--md">
            {{-- Header con Kicker Temático y Badge de Estado --}}
            <header class="flex items-start justify-between border-b border-zinc-200/90 bg-zinc-50/70 p-4 sm:p-5 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 bg-emerald-100/80 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950/60 dark:text-emerald-300">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Registro de Backup</span>
                        </span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $statusTone['badge'] }}">
                            {{ match($viewingBackup->status) { 'completed' => 'Completado', 'failed' => 'Fallido', 'running' => 'En Proceso', default => ucfirst($viewingBackup->status) } }}
                        </span>
                    </div>
                    <h3 class="text-xl font-black text-zinc-900 dark:text-zinc-50">{{ $backupTypeLabel($viewingBackup->type) }}</h3>
                    <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $viewingBackup->created_at->format('d/m/Y · H:i:s') }}</span>
                    </p>
                </div>
                <button type="button" wire:click="closeBackupDetails" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200/90 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:border-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 transition cursor-pointer" aria-label="Cerrar modal">
                    <svg class="h-4.5 w-4.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </header>

            <div class="agro-dialog__scroll space-y-4 p-4 sm:p-5">
                {{-- Cuadrícula de Métricas con Colores Semánticos Diferenciados --}}
                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                    {{-- 1. Origen --}}
                    <div class="rounded-xl border border-emerald-500/25 bg-emerald-50/60 p-3 dark:border-emerald-500/20 dark:bg-emerald-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Origen</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ $backupTriggerLabel($viewingBackup->trigger) }}</strong>
                    </div>

                    {{-- 2. Usuario --}}
                    <div class="rounded-xl border border-emerald-500/25 bg-emerald-50/60 p-3 dark:border-emerald-500/20 dark:bg-emerald-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Usuario</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ $viewingBackup->requester?->name ?? 'Sistema' }}</strong>
                    </div>

                    {{-- 3. Formato --}}
                    <div class="rounded-xl border border-indigo-500/25 bg-indigo-50/60 p-3 dark:border-indigo-500/20 dark:bg-indigo-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Formato</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ strtoupper($viewingBackup->format ?? 'SQL') }}</strong>
                    </div>

                    {{-- 4. Tamaño --}}
                    <div class="rounded-xl border border-indigo-500/25 bg-indigo-50/60 p-3 dark:border-indigo-500/20 dark:bg-indigo-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Tamaño</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ $formatBackupSize($viewingBackup->size_bytes) }}</strong>
                    </div>

                    {{-- 5. Registros --}}
                    <div class="rounded-xl border border-sky-500/25 bg-sky-50/60 p-3 dark:border-sky-500/20 dark:bg-sky-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-sky-700 dark:text-sky-400">Registros BD</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ number_format($viewingBackup->record_count ?? 0) }}</strong>
                    </div>

                    {{-- 6. Archivos / Fotos --}}
                    <div class="rounded-xl border border-sky-500/25 bg-sky-50/60 p-3 dark:border-sky-500/20 dark:bg-sky-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-sky-700 dark:text-sky-400">Archivos Fotos</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ number_format($viewingBackup->photo_count ?? 0) }}</strong>
                    </div>

                    {{-- 7. Duración --}}
                    <div class="rounded-xl border border-amber-500/25 bg-amber-50/60 p-3 dark:border-amber-500/20 dark:bg-amber-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400">Duración</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ $detailSeconds !== null ? $detailSeconds.' s' : 'No disponible' }}</strong>
                    </div>

                    {{-- 8. Motor BD --}}
                    <div class="rounded-xl border border-amber-500/25 bg-amber-50/60 p-3 dark:border-amber-500/20 dark:bg-amber-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400">Motor BD</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ strtoupper($viewingBackup->database_driver ?? 'MySQL') }}</strong>
                    </div>

                    {{-- 9. Restauraciones --}}
                    <div class="rounded-xl border border-purple-500/25 bg-purple-50/60 p-3 dark:border-purple-500/20 dark:bg-purple-950/25 transition">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400">Restaurado</small>
                        <strong class="mt-1 block break-words text-xs font-black text-zinc-900 dark:text-zinc-100">{{ (int) ($viewingBackup->restores_count ?? 0) > 0 ? ((int) $viewingBackup->restores_count).' veces' : 'Nunca' }}</strong>
                    </div>
                </div>

                {{-- Componentes Protegidos --}}
                <div class="rounded-2xl border border-zinc-200/90 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-zinc-600 dark:text-zinc-400 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Componentes protegidos en este archivo</span>
                        </small>
                        <span class="text-[10px] font-bold text-zinc-500">Alcance de respaldo</span>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-100/90 px-3 py-1.5 text-xs font-bold text-emerald-950 dark:border-emerald-500/30 dark:bg-emerald-950/60 dark:text-emerald-200 shadow-2xs">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span>Fundo Activo (Animales, Producción, Sanidad, Finanzas)</span>
                        </span>
                        @if($detailComponents['web'] ?? false)
                            <span class="inline-flex items-center gap-1.5 rounded-xl border border-sky-500/30 bg-sky-100/90 px-3 py-1.5 text-xs font-bold text-sky-950 dark:border-sky-500/30 dark:bg-sky-950/60 dark:text-sky-200 shadow-2xs">
                                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                <span>Gestión Web Institucional</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Integridad Criptográfica SHA-256 --}}
                <div x-data="{ copied: false }" class="rounded-2xl border border-emerald-500/30 bg-emerald-50/60 p-4 dark:border-emerald-500/25 dark:bg-emerald-950/25 space-y-2">
                    <div class="flex items-center justify-between">
                        <small class="block text-[9.5px] font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Integridad Criptográfica SHA-256</span>
                        </small>
                        <strong class="text-xs font-bold {{ $viewingBackup->integrity_verified_at ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-600 dark:text-zinc-400' }}">
                            {{ $viewingBackup->integrity_verified_at ? '✓ Verificada '.$viewingBackup->integrity_verified_at->format('d/m/Y H:i') : ($viewingBackup->checksum_sha256 ? 'Registrada en base de datos' : 'No disponible') }}
                        </strong>
                    </div>

                    @if($viewingBackup->checksum_sha256)
                        <div class="flex items-center justify-between gap-2 rounded-xl border border-zinc-300/90 bg-white p-2.5 font-mono text-[11px] font-bold text-zinc-900 shadow-2xs dark:border-zinc-800 dark:bg-zinc-950 dark:text-emerald-300 break-all">
                            <span>{{ $viewingBackup->checksum_sha256 }}</span>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $viewingBackup->checksum_sha256 }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-50 px-2 py-1 text-[10px] font-extrabold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition cursor-pointer"
                                    title="Copiar Checksum">
                                <span x-text="copied ? '¡Copiado!' : 'Copiar'"></span>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Mensaje de Error si existiera --}}
                @if($viewingBackup->error_message)
                    <div class="rounded-2xl border border-rose-300 bg-rose-50/90 p-4 text-xs text-rose-900 dark:border-rose-500/30 dark:bg-rose-950/40 dark:text-rose-200 space-y-1">
                        <strong class="block font-black flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Error registrado en la generación:</span>
                        </strong>
                        <p class="break-words font-medium">{{ $viewingBackup->error_message }}</p>
                    </div>
                @endif

                {{-- Nombre de Archivo --}}
                <div class="flex items-center justify-between gap-2 rounded-xl border border-zinc-200/90 bg-zinc-100/80 px-3.5 py-2.5 dark:border-zinc-800 dark:bg-zinc-900 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="h-4 w-4 shrink-0 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="truncate font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $viewingBackup->filename ?? 'Archivo no generado' }}</span>
                    </div>
                    <span class="text-[10px] font-bold text-zinc-500 shrink-0">{{ strtoupper($viewingBackup->format ?? 'SQL') }}</span>
                </div>
            </div>

            {{-- Footer de Acciones --}}
            <footer class="flex flex-col-reverse gap-2.5 border-t border-zinc-200/90 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:flex-row sm:justify-end sm:items-center">
                <button type="button" wire:click="closeBackupDetails"
                        class="inline-flex h-10 items-center justify-center px-4 rounded-xl border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 text-xs sm:text-sm font-bold transition active:scale-95 cursor-pointer">
                    Cerrar
                </button>
                @if($viewingBackup->status === 'completed' && $viewingBackup->format === 'zip' && auth()->user()->tienePermiso('ajustes', 'restaurar'))
                    <button type="button" wire:click="openRestoreModal({{ $viewingBackup->id }})"
                            class="inline-flex h-10 items-center justify-center gap-1.5 px-4 rounded-xl border border-amber-500/40 bg-amber-50 text-amber-900 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-900/60 text-xs sm:text-sm font-bold transition active:scale-95 cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Restaurar</span>
                    </button>
                @endif
                @if($viewingBackup->status === 'completed' && $viewingBackup->path && auth()->user()->tienePermiso('ajustes', 'exportar'))
                    <a href="{{ route('ajustes.backups.download', $viewingBackup) }}"
                       class="inline-flex h-10 items-center justify-center gap-2 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs sm:text-sm shadow-md shadow-emerald-600/25 transition active:scale-95 cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Descargar</span>
                    </a>
                @endif
            </footer>
        </section>
    </div>
@endif

@if($showRestoreModal)
    <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay" @click.self="closeRestoreModal">
        <section role="dialog" aria-modal="true" aria-label="Restaurar backup" class="agro-dialog agro-dialog--md">
            <header class="flex items-start justify-between border-b border-zinc-200/90 bg-amber-50/60 p-4 sm:p-5 dark:border-zinc-800 dark:bg-amber-950/30">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/40 bg-amber-100/90 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-900 dark:border-amber-500/40 dark:bg-amber-950/70 dark:text-amber-200">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Operación Crítica</span>
                    </span>
                    <h3 class="mt-1.5 text-xl font-black text-zinc-900 dark:text-zinc-50">Restaurar Copia de Seguridad</h3>
                    <p class="mt-1 text-xs font-mono font-bold text-zinc-600 dark:text-zinc-400">{{ $restoreSummary['filename'] ?? '' }}</p>
                </div>
                <button type="button" wire:click="closeRestoreModal" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200/90 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:border-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 transition cursor-pointer" aria-label="Cerrar modal">
                    <svg class="h-4.5 w-4.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </header>

            <div class="agro-dialog__scroll space-y-4 p-4 sm:p-5">
                <div class="rounded-2xl border border-amber-300/80 bg-amber-50/80 p-4 text-xs leading-relaxed text-amber-950 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-100 space-y-1">
                    <strong class="block font-black text-sm">Esta operación reemplazará los datos actuales del fundo.</strong>
                    <p>Puedes generar una copia de seguridad previa del estado actual (recomendado) para poder revertir en cualquier momento. Usuarios, permisos y sesiones no se alteran.</p>
                    @if($restoreSummary['components']['web'] ?? false)
                        <p class="font-bold text-sky-800 dark:text-sky-300 pt-1">Gestión web institucional también se restaurará.</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                    <div class="rounded-xl border border-zinc-200/90 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                        <small class="block text-[9.5px] font-black uppercase text-zinc-500">Fecha</small>
                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $restoreSummary['created_at'] ?? '—' }}</strong>
                    </div>
                    <div class="rounded-xl border border-zinc-200/90 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                        <small class="block text-[9.5px] font-black uppercase text-zinc-500">Tipo</small>
                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $backupTypeLabel($restoreSummary['type'] ?? null) }}</strong>
                    </div>
                    <div class="rounded-xl border border-sky-500/30 bg-sky-50/60 p-3 dark:border-sky-500/20 dark:bg-sky-950/30">
                        <small class="block text-[9.5px] font-black uppercase text-sky-700 dark:text-sky-400">Registros BD</small>
                        <strong class="text-xs font-black text-zinc-900 dark:text-zinc-100">{{ number_format($restoreSummary['records'] ?? 0) }}</strong>
                    </div>
                    <div class="rounded-xl border border-sky-500/30 bg-sky-50/60 p-3 dark:border-sky-500/20 dark:bg-sky-950/30">
                        <small class="block text-[9.5px] font-black uppercase text-sky-700 dark:text-sky-400">Archivos Fotos</small>
                        <strong class="text-xs font-black text-zinc-900 dark:text-zinc-100">{{ number_format($restoreSummary['files'] ?? 0) }}</strong>
                    </div>
                </div>

                <fieldset class="space-y-2">
                    <legend class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Contenido a restaurar:</legend>
                    <div class="grid gap-2 sm:grid-cols-3">
                        @foreach($restoreModes as $mode)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model="restoreMode" value="{{ $mode }}" class="peer sr-only">
                                <span class="flex min-h-11 items-center justify-center rounded-xl border border-zinc-200/90 bg-white p-2.5 text-center text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-950 peer-checked:ring-2 peer-checked:ring-amber-500/30 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:peer-checked:border-amber-400 dark:peer-checked:bg-amber-400/10 dark:peer-checked:text-amber-100">
                                    {{ $backupTypeLabel($mode) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('restoreMode')<p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p>@enderror
                </fieldset>

                <div class="space-y-1.5">
                    <label for="restore-pwd" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                        Confirma con tu contraseña de administrador:
                    </label>
                    <input id="restore-pwd" type="password" wire:model="restorePassword" autocomplete="current-password"
                           class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                           placeholder="Ingresa tu contraseña...">
                    @error('restorePassword')<p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p>@enderror
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-amber-200 bg-amber-50/50 p-3.5 transition hover:bg-amber-50 dark:border-amber-500/25 dark:bg-amber-950/20 dark:hover:bg-amber-950/30">
                    <input type="checkbox" wire:model="createPreBackup" class="h-4.5 w-4.5 rounded text-amber-600 focus:ring-amber-500">
                    <div class="text-xs">
                        <strong class="block font-black text-amber-950 dark:text-amber-200">Crear copia de seguridad previa</strong>
                        <span class="text-zinc-600 dark:text-zinc-400 text-[11px]">Genera un respaldo automático del estado actual antes de aplicar la restauración.</span>
                    </div>
                </label>
            </div>

            <footer class="flex flex-col-reverse gap-2.5 border-t border-zinc-200/90 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:flex-row sm:justify-end sm:items-center">
                <button type="button" wire:click="closeRestoreModal" wire:loading.attr="disabled" wire:target="restoreBackup"
                        class="inline-flex h-10 items-center justify-center px-4 rounded-xl border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 text-xs sm:text-sm font-bold transition active:scale-95 cursor-pointer">
                    Cancelar
                </button>
                <button type="button" wire:click="restoreBackup" wire:loading.attr="disabled" wire:target="restoreBackup"
                        class="inline-flex h-10 items-center justify-center gap-2 px-6 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-black text-xs sm:text-sm shadow-md shadow-amber-600/25 transition active:scale-95 disabled:opacity-60 cursor-pointer">
                    <span wire:loading.remove wire:target="restoreBackup">Restaurar ahora</span>
                    <span wire:loading wire:target="restoreBackup">Restaurando...</span>
                </button>
            </footer>
        </section>
    </div>
@endif
