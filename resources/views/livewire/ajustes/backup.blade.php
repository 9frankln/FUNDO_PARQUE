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
@endphp

<section class="space-y-5">
    <div class="agro-card relative overflow-hidden p-4 sm:p-6">
        <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3 sm:gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-white shadow-lg shadow-emerald-800/20 dark:bg-emerald-400 dark:text-emerald-950">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 5v5h10V5M8 15h8"/></svg>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2"><p class="agro-kicker">Continuidad de datos</p><span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold {{ $backupSettings['enabled'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ $backupSettings['enabled'] ? 'AUTOMÁTICO ACTIVO' : 'AUTOMÁTICO PAUSADO' }}</span></div>
                    <h2 class="mt-1 text-xl font-extrabold sm:text-2xl">Centro de respaldos</h2>
                    <p class="mt-1 max-w-2xl text-xs leading-5 text-zinc-500 sm:text-sm">Respaldos ZIP cifrados del fundo, Gestión web y Auditoría, con contenido y frecuencia configurables.</p>
                </div>
            </div>
            @if(auth()->user()->tienePermiso('ajustes', 'exportar'))
                <button wire:click="generateBackup" wire:loading.attr="disabled" wire:target="generateBackup" class="agro-button w-full lg:w-auto">
                    <span wire:loading.remove wire:target="generateBackup" class="inline-flex items-center gap-2"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0-4-4m4 4 4-4M5 20h14"/></svg>Generar {{ $backupTypeLabel($backupScope) }}</span>
                    <span wire:loading wire:target="generateBackup">Generando ZIP...</span>
                </button>
            @endif
        </div>
        <div wire:loading wire:target="generateBackup,uploadBackup,restoreBackup" class="mt-5 rounded-2xl border border-emerald-500/25 bg-emerald-50 p-3 dark:border-emerald-400/20 dark:bg-emerald-400/10">
            <div class="flex items-center gap-3"><svg class="h-5 w-5 animate-spin text-emerald-700 dark:text-emerald-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"/></svg><div class="min-w-0 flex-1"><div class="h-1.5 overflow-hidden rounded-full bg-emerald-200 dark:bg-emerald-950"><div class="h-full w-2/3 animate-pulse rounded-full bg-emerald-600 dark:bg-emerald-400"></div></div><p class="mt-1.5 text-[10px] font-semibold text-emerald-800 dark:text-emerald-200">Procesando y verificando integridad. No cierres pantalla.</p></div></div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <article class="agro-metric-card col-span-2 rounded-2xl border p-4 sm:col-span-1"><span class="text-[9px] font-bold uppercase tracking-wider text-zinc-500">Último backup</span><strong class="mt-2 block text-sm">{{ $backupOverview['last']?->created_at?->format('d/m/Y H:i') ?? 'Sin copias' }}</strong><small class="mt-1 block text-zinc-500">{{ $backupOverview['last'] ? $backupTypeLabel($backupOverview['last']->type) : 'Genera primera copia' }}</small></article>
        <article class="agro-metric-card rounded-2xl border p-4"><span class="text-[9px] font-bold uppercase tracking-wider text-zinc-500">Próximo</span><strong class="mt-2 block text-sm">{{ $backupOverview['next']?->format('d/m H:i') ?? 'No programado' }}</strong><small class="mt-1 block text-zinc-500">{{ $backupTypeLabel($backupSettings['scope']) }}</small></article>
        <article class="agro-metric-card rounded-2xl border p-4"><span class="text-[9px] font-bold uppercase tracking-wider text-zinc-500">Espacio usado</span><strong class="mt-2 block text-sm">{{ $formatBackupSize($backupOverview['size_bytes'] ?? 0) }}</strong><small class="mt-1 block text-zinc-500">Servidor privado</small></article>
        <article class="agro-metric-card rounded-2xl border p-4"><span class="text-[9px] font-bold uppercase tracking-wider text-zinc-500">Copias</span><strong class="mt-2 block text-sm">{{ $backupOverview['count'] ?? 0 }}</strong><small class="mt-1 block text-zinc-500">{{ $backupSettings['retention_count'] }} máx. por tipo</small></article>
        <article class="agro-metric-card rounded-2xl border p-4"><span class="text-[9px] font-bold uppercase tracking-wider text-zinc-500">Último error</span><strong class="mt-2 block text-sm {{ ($backupOverview['last_error'] ?? null) ? 'text-rose-600 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ ($backupOverview['last_error'] ?? null) ? $backupOverview['last_error']->failed_at?->format('d/m H:i') : 'Sin errores' }}</strong><small class="mt-1 block text-zinc-500">{{ ($backupOverview['last_error'] ?? null) ? 'Ver detalles' : 'Sistema estable' }}</small></article>
    </div>

    <div class="grid gap-5 lg:grid-cols-12">
        <aside class="order-2 space-y-5 lg:col-span-4 xl:col-span-4 lg:order-1">
            <form wire:submit="saveBackupSettings" class="agro-card overflow-hidden">
                <header class="border-b border-zinc-200 p-4 dark:border-zinc-800"><p class="agro-kicker">Programación</p><h3 class="mt-1 text-lg font-extrabold">Respaldo automático</h3><p class="mt-1 text-xs text-zinc-500">Elige cuándo ejecutar y qué contenido proteger.</p></header>
                <div class="space-y-4 p-4">
                    <label class="flex items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-400/20 dark:bg-emerald-400/10"><span><strong class="block text-xs">Generación automática</strong><small class="text-zinc-500">Scheduler cada 15 minutos</small></span><input type="checkbox" wire:model="backupSettings.enabled" class="agro-checkbox h-5 w-5 rounded shrink-0"></label>
                    <fieldset><legend class="mb-2 text-xs font-bold">Frecuencia</legend><div class="grid grid-cols-2 gap-2 sm:grid-cols-2">@foreach(['custom' => 'Personalizada', 'daily' => 'Diaria', 'weekly' => 'Semanal', 'monthly' => 'Mensual'] as $schedule => $label)<label class="cursor-pointer min-w-0"><input type="radio" wire:model.live="backupSettings.schedule" value="{{ $schedule }}" class="peer sr-only"><span class="flex min-h-10 items-center justify-center rounded-xl border border-zinc-200 px-1.5 text-center text-[10px] sm:text-xs font-bold peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 dark:border-zinc-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/10 dark:peer-checked:text-emerald-200 truncate">{{ $label }}</span></label>@endforeach</div></fieldset>
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
                    <fieldset><legend class="mb-2 text-xs font-bold">Contenido automático</legend><div class="space-y-2">@foreach(['database' => 'Datos del fundo', 'files' => 'Fotos y archivos', 'complete' => 'Todo el fundo'] as $type => $label)<label class="block cursor-pointer"><input type="radio" wire:model="backupSettings.scope" value="{{ $type }}" class="peer sr-only"><span class="flex items-center justify-between rounded-xl border border-zinc-200 px-3 py-2.5 text-xs font-bold peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 dark:border-zinc-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/10 dark:peer-checked:text-emerald-200"><span>{{ $label }}</span><span class="text-[10px] opacity-60">{{ $type === 'complete' ? 'DATOS + ARCHIVOS' : ($type === 'files' ? 'SOLO ARCHIVOS' : 'SOLO DATOS') }}</span></span></label>@endforeach</div></fieldset>
                    <fieldset><legend class="mb-2 text-xs font-bold">Componentes adicionales</legend><div class="space-y-2"><label class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><input type="checkbox" wire:model="backupSettings.include_web" class="agro-checkbox mt-0.5 h-4 w-4 rounded"><span><strong class="block text-xs">Gestión web</strong><small class="text-[10px] leading-4 text-zinc-500">Textos, identidad, medios y encuadres.</small></span></label><label class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><input type="checkbox" wire:model="backupSettings.include_audit" class="agro-checkbox mt-0.5 h-4 w-4 rounded"><span><strong class="block text-xs">Auditoría</strong><small class="text-[10px] leading-4 text-zinc-500">Evidencia cifrada de solo lectura.</small></span></label></div></fieldset>
                    <label class="block"><span class="mb-1 block text-xs font-bold">Copias por tipo</span><input type="number" min="2" max="100" wire:model="backupSettings.retention_count" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs dark:border-zinc-700">@error('backupSettings.retention_count')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror</label>
                    @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))<button class="agro-button w-full" type="submit">Guardar programación</button>@endif
                    <p class="rounded-xl bg-zinc-50 p-3 text-[10px] leading-4 text-zinc-500 dark:bg-zinc-900">El servidor revisa la programación cada 15 minutos. Auditoría se conserva, pero nunca se rebobina durante una restauración.</p>
                </div>
            </form>

            @if(auth()->user()->tienePermiso('ajustes', 'restaurar'))
                <form wire:submit="uploadBackup" class="agro-card p-4" x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true; progress = 0" x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <p class="agro-kicker">Importar</p><h3 class="mt-1 text-lg font-extrabold">Subir backup</h3><p class="mt-1 text-xs leading-5 text-zinc-500">Acepta ZIP firmado por esta instalación y perteneciente al fundo activo.</p>
                    <label class="mt-4 flex cursor-pointer flex-col items-center rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-5 text-center transition hover:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-900"><svg class="h-7 w-7 text-emerald-700 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L8 8m4-4 4 4M5 14v5h14v-5"/></svg><strong class="mt-2 text-xs">Seleccionar archivo .zip</strong><small class="mt-1 max-w-full truncate text-zinc-500">{{ $backupUpload?->getClientOriginalName() ?? 'Máximo 10 GB' }}</small><input type="file" wire:model="backupUpload" accept=".zip,application/zip" class="sr-only"></label>
                    @error('backupUpload')<p class="mt-2 text-xs text-rose-500">{{ $message }}</p>@enderror
                    <div x-cloak x-show="uploading" class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-400/20 dark:bg-emerald-400/10"><div class="flex items-center justify-between gap-3 text-[10px] font-bold text-emerald-800 dark:text-emerald-200"><span>Subiendo al servidor...</span><span x-text="`${progress}%`"></span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-emerald-200 dark:bg-emerald-950"><div class="h-full rounded-full bg-emerald-600 transition-[width] duration-150 dark:bg-emerald-400" x-bind:style="`width: ${progress}%`"></div></div></div>
                    <p class="mt-3 text-[10px] leading-4 text-zinc-500">Sin límite artificial de velocidad. La rapidez depende de tu conexión y del servidor; mantén esta pantalla abierta hasta finalizar.</p>
                    <button type="submit" wire:loading.attr="disabled" wire:target="backupUpload,uploadBackup" class="agro-button-secondary mt-3 w-full">Validar e importar</button>
                </form>
            @endif
        </aside>

        <div class="order-1 min-w-0 space-y-5 lg:col-span-8 xl:col-span-8 lg:order-2">
            <div>
                <div class="mb-3"><p class="agro-kicker">Tipo de copia</p><h3 class="mt-1 text-lg font-extrabold">Selecciona contenido</h3></div>
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
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition {{ $backupIncludeWeb ? 'border-emerald-600 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-400/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}"><input type="checkbox" wire:model.live="backupIncludeWeb" class="agro-checkbox mt-0.5 h-4 w-4 rounded"><span><strong class="block text-sm">Incluir Gestión web</strong><small class="mt-1 block text-[11px] leading-4 text-zinc-500">Secciones públicas, identidad, encuadres y archivos web cuando el tipo incluya archivos.</small></span></label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition {{ $backupIncludeAudit ? 'border-emerald-600 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-400/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}"><input type="checkbox" wire:model.live="backupIncludeAudit" class="agro-checkbox mt-0.5 h-4 w-4 rounded"><span><strong class="block text-sm">Incluir Auditoría</strong><small class="mt-1 block text-[11px] leading-4 text-zinc-500">Conserva el historial como evidencia cifrada; no reemplaza eventos al restaurar.</small></span></label>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <article class="agro-card p-4"><p class="agro-kicker">Seguridad real</p><h3 class="mt-1 text-base font-extrabold">ZIP protegido</h3><div class="mt-3 grid grid-cols-2 gap-2 text-[10px] font-bold"><span class="rounded-lg bg-emerald-50 p-2 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">AES-256</span><span class="rounded-lg bg-emerald-50 p-2 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">SHA-256</span><span class="rounded-lg bg-zinc-50 p-2 text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">Firma HMAC</span><span class="rounded-lg bg-zinc-50 p-2 text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">Aislamiento por fundo</span></div></article>
                <article class="agro-card p-4"><p class="agro-kicker">Restauración segura</p><h3 class="mt-1 text-base font-extrabold">Recuperación con retorno</h3><p class="mt-2 text-[11px] leading-5 text-zinc-500">Antes de restaurar se genera backup automático del estado actual. SQL subido nunca se ejecuta; restauración usa datos estructurados allowlist.</p></article>
            </div>

            <div>
                <div class="mb-3"><p class="agro-kicker">Auditoría</p><h3 class="mt-1 text-lg font-extrabold">Historial</h3><p class="mt-1 text-xs text-zinc-500">Descarga, verifica, restaura o elimina cada backup.</p></div>
                <div class="agro-record-surface overflow-hidden rounded-2xl border">
                    <div class="hidden overflow-x-auto lg:block">
                        <table class="w-full min-w-[900px] text-left">
                            <thead class="agro-record-header text-[10px] font-bold uppercase tracking-wider"><tr><th class="p-4">Fecha</th><th class="p-4">Contenido</th><th class="p-4">Tamaño</th><th class="p-4">Duración</th><th class="p-4">Estado</th><th class="p-4">Integridad</th><th class="p-4 text-right">Acciones</th></tr></thead>
                            <tbody class="agro-record-list text-sm">
                                @forelse($backups as $backup)
                                    @php
                                        $seconds = $backup->started_at && $backup->completed_at ? $backup->started_at->diffInSeconds($backup->completed_at) : null;
                                        $components = $backup->components ?? [];
                                    @endphp
                                    <tr wire:key="backup-{{ $backup->id }}">
                                        <td class="p-4"><strong class="block">{{ $backup->created_at->format('d/m/Y') }}</strong><span class="text-xs text-zinc-500">{{ $backup->created_at->format('H:i:s') }}</span></td>
                                        <td class="p-4"><strong class="block text-xs">{{ $backupTypeLabel($backup->type) }}</strong><span class="text-[10px] text-zinc-500">{{ $backupTriggerLabel($backup->trigger) }} · {{ strtoupper($backup->format ?? 'sql') }} · {{ $backup->requester?->name ?? 'Sistema' }}</span>@if($components)<span class="mt-1 flex flex-wrap gap-1">@if($components['web'] ?? false)<span class="rounded bg-sky-100 px-1.5 py-0.5 text-[8px] font-bold text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">WEB</span>@endif @if($components['audit'] ?? false)<span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[8px] font-bold text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300">AUDITORÍA</span>@endif</span>@endif</td>
                                        <td class="p-4"><strong class="block text-xs">{{ $formatBackupSize($backup->size_bytes) }}</strong><span class="text-[10px] text-zinc-500">{{ number_format($backup->record_count ?? 0) }} reg. · {{ number_format($backup->photo_count ?? 0) }} archivos</span></td>
                                        <td class="p-4 text-xs">{{ $seconds !== null ? ($seconds < 60 ? $seconds.' s' : floor($seconds / 60).' min') : '—' }}</td>
                                        <td class="p-4"><x-status-badge :value="$backup->status" :label="match($backup->status) { 'completed' => 'Completado', 'failed' => 'Fallido', 'running' => 'Generando', default => 'Pendiente' }" :tone="match($backup->status) { 'completed' => 'emerald', 'failed' => 'rose', 'running' => 'amber', default => 'slate' }" /></td>
                                        <td class="p-4"><span class="inline-flex items-center gap-1 text-[10px] font-bold {{ $backup->integrity_verified_at ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-500' }}"><span class="h-1.5 w-1.5 rounded-full {{ $backup->integrity_verified_at ? 'bg-emerald-500' : ($backup->checksum_sha256 ? 'bg-amber-400' : 'bg-zinc-400') }}"></span>{{ $backup->integrity_verified_at ? 'Verificado' : ($backup->checksum_sha256 ? 'SHA registrado' : 'Pendiente') }}</span></td>
                                        <td class="p-4"><div class="flex justify-end gap-1.5"><x-table-action type="view" wire:click="openBackupDetails({{ $backup->id }})" label="Ver detalles" />@if($backup->status === 'completed' && $backup->path && auth()->user()->tienePermiso('ajustes', 'exportar'))<x-table-action type="verify" wire:click="verifyBackup({{ $backup->id }})" label="Verificar integridad" /><x-table-action type="download" href="{{ route('ajustes.backups.download', $backup) }}" label="Descargar {{ strtoupper($backup->format ?? 'SQL') }}" />@endif @if($backup->status === 'completed' && $backup->format === 'zip' && auth()->user()->tienePermiso('ajustes', 'restaurar'))<x-table-action type="restore" wire:click="openRestoreModal({{ $backup->id }})" label="Restaurar backup" />@endif @if(auth()->user()->tienePermiso('ajustes', 'eliminar'))<x-table-action type="delete" x-on:click.prevent="confirmDelete('¿Eliminar este backup?', 'Archivo e historial se eliminarán permanentemente.').then((res) => { if (res.isConfirmed) $wire.deleteBackup({{ $backup->id }}) })" label="Eliminar backup" />@endif</div></td>
                                    </tr>
                                @empty<tr><td colspan="7" class="p-10 text-center text-sm text-zinc-500">Sin backups. Selecciona tipo y genera primera copia.</td></tr>@endforelse
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
                                @if($components)<div class="flex flex-wrap gap-1">@if($components['web'] ?? false)<span class="rounded bg-sky-100 px-2 py-1 text-[9px] font-bold text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">Gestión web</span>@endif @if($components['audit'] ?? false)<span class="rounded bg-indigo-100 px-2 py-1 text-[9px] font-bold text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300">Auditoría</span>@endif</div>@endif
                                <div class="grid grid-cols-3 gap-2 rounded-xl bg-zinc-50 p-3 text-center dark:bg-zinc-900"><span><small class="block text-[9px] uppercase text-zinc-500">Tamaño</small><strong class="text-[11px]">{{ $formatBackupSize($backup->size_bytes) }}</strong></span><span><small class="block text-[9px] uppercase text-zinc-500">Contenido</small><strong class="text-[11px]">{{ number_format(($backup->record_count ?? 0) + ($backup->photo_count ?? 0)) }}</strong></span><span><small class="block text-[9px] uppercase text-zinc-500">Integridad</small><strong class="text-[11px]">{{ $backup->integrity_verified_at ? 'Verificada' : ($backup->checksum_sha256 ? 'Registrada' : '—') }}</strong></span></div>
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
                        @empty<div class="p-8 text-center text-sm text-zinc-500">Sin backups almacenados.</div>@endforelse
                    </div>
                </div>
                <div class="agro-table-footer"><div class="agro-table-size"><span>Mostrar</span><x-filter-select model="backupsPerPage" :options="$perPageOptions" tone="emerald" live compact /></div><div class="min-w-0">{{ $backups->links('components.pagination') }}</div></div>
            </div>
        </div>
    </div>
</section>

@if($showBackupDetails && $viewingBackup)
    @php
        $detailSeconds = $viewingBackup->started_at && $viewingBackup->completed_at ? $viewingBackup->started_at->diffInSeconds($viewingBackup->completed_at) : null;
        $detailComponents = $viewingBackup->components ?? [];
    @endphp
    <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
        <section role="dialog" aria-modal="true" aria-label="Detalles del backup" class="agro-dialog agro-dialog--md">
            <header class="flex items-start justify-between border-b border-zinc-200 p-4 dark:border-zinc-800 sm:px-5"><div><p class="agro-kicker">Registro de backup</p><h3 class="mt-1 text-lg font-extrabold">{{ $backupTypeLabel($viewingBackup->type) }}</h3><p class="mt-1 text-xs text-zinc-500">{{ $viewingBackup->created_at->format('d/m/Y · H:i:s') }}</p></div><button wire:click="closeBackupDetails" class="agro-icon-button !h-9 !w-9" aria-label="Cerrar">&times;</button></header>
            <div class="agro-dialog__scroll space-y-4 p-4 sm:p-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">@foreach(['Origen' => $backupTriggerLabel($viewingBackup->trigger), 'Usuario' => $viewingBackup->requester?->name ?? 'Sistema', 'Formato' => strtoupper($viewingBackup->format ?? 'SQL'), 'Tamaño' => $formatBackupSize($viewingBackup->size_bytes), 'Registros' => number_format($viewingBackup->record_count ?? 0), 'Archivos' => number_format($viewingBackup->photo_count ?? 0), 'Duración' => $detailSeconds !== null ? $detailSeconds.' segundos' : 'No disponible', 'Motor' => strtoupper($viewingBackup->database_driver ?? 'N/D'), 'Estado' => ucfirst($viewingBackup->status)] as $label => $value)<div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><small class="block text-[9px] font-bold uppercase tracking-wider text-zinc-500">{{ $label }}</small><strong class="mt-1 block break-words text-xs">{{ $value }}</strong></div>@endforeach</div>
                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700"><small class="block text-[9px] font-bold uppercase tracking-wider text-zinc-500">Componentes protegidos</small><div class="mt-2 flex flex-wrap gap-2"><span class="rounded-lg bg-zinc-100 px-2.5 py-1.5 text-[10px] font-bold dark:bg-zinc-800">Fundo</span>@if($detailComponents['web'] ?? false)<span class="rounded-lg bg-sky-100 px-2.5 py-1.5 text-[10px] font-bold text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">Gestión web</span>@endif @if($detailComponents['audit'] ?? false)<span class="rounded-lg bg-indigo-100 px-2.5 py-1.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300">Auditoría · solo archivo</span>@endif</div></div>
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900"><small class="block text-[9px] font-bold uppercase tracking-wider text-zinc-500">Integridad SHA-256</small><strong class="mt-1 block text-xs {{ $viewingBackup->integrity_verified_at ? 'text-emerald-700 dark:text-emerald-300' : '' }}">{{ $viewingBackup->integrity_verified_at ? 'Verificada '.$viewingBackup->integrity_verified_at->format('d/m/Y H:i') : ($viewingBackup->checksum_sha256 ? 'Registrada, pendiente de verificación' : 'No disponible') }}</strong>@if($viewingBackup->checksum_sha256)<code class="mt-3 block break-all rounded-lg bg-white p-2 text-[10px] dark:bg-zinc-950">{{ $viewingBackup->checksum_sha256 }}</code>@endif</div>
                @if($viewingBackup->error_message)<div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200"><strong class="block">Error registrado</strong><p class="mt-1 break-words">{{ $viewingBackup->error_message }}</p></div>@endif
                <p class="break-all text-xs font-semibold">{{ $viewingBackup->filename ?? 'Archivo no generado' }}</p>
            </div>
            <footer class="flex flex-col-reverse gap-2 border-t border-zinc-200 p-4 dark:border-zinc-800 sm:flex-row sm:justify-end"><button wire:click="closeBackupDetails" class="agro-button-secondary">Cerrar</button>@if($viewingBackup->status === 'completed' && $viewingBackup->format === 'zip' && auth()->user()->tienePermiso('ajustes', 'restaurar'))<button wire:click="openRestoreModal({{ $viewingBackup->id }})" class="agro-button-secondary">Restaurar</button>@endif @if($viewingBackup->status === 'completed' && $viewingBackup->path && auth()->user()->tienePermiso('ajustes', 'exportar'))<a href="{{ route('ajustes.backups.download', $viewingBackup) }}" class="agro-button">Descargar</a>@endif</footer>
        </section>
    </div>
@endif

@if($showRestoreModal)
    <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
        <section role="dialog" aria-modal="true" aria-label="Restaurar backup" class="agro-dialog agro-dialog--md">
            <header class="flex items-start justify-between border-b border-zinc-200 p-4 dark:border-zinc-800 sm:px-5"><div><p class="text-xs font-bold uppercase tracking-[.16em] text-amber-700 dark:text-amber-300">Operación crítica</p><h3 class="mt-1 text-xl font-extrabold">Restaurar backup</h3><p class="mt-1 text-xs text-zinc-500">{{ $restoreSummary['filename'] ?? '' }}</p></div><button wire:click="closeRestoreModal" class="agro-icon-button !h-9 !w-9" aria-label="Cerrar">&times;</button></header>
            <div class="agro-dialog__scroll space-y-4 p-4 sm:p-5">
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-xs leading-5 text-amber-900 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-100"><strong class="block">Esta operación reemplazará el contenido seleccionado.</strong> Antes de modificar algo, el sistema generará un respaldo automático del estado actual. Usuarios, accesos, sesiones y otros fundos no se alteran.@if($restoreSummary['components']['web'] ?? false) Gestión web se restaurará al elegir datos o todo.@endif @if($restoreSummary['components']['audit'] ?? false) Auditoría se conserva en el ZIP, pero sus eventos actuales nunca se eliminan ni retroceden.@endif</div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4"><div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><small class="block text-[9px] uppercase text-zinc-500">Fecha</small><strong class="text-xs">{{ $restoreSummary['created_at'] ?? '—' }}</strong></div><div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><small class="block text-[9px] uppercase text-zinc-500">Tipo</small><strong class="text-xs">{{ $backupTypeLabel($restoreSummary['type'] ?? null) }}</strong></div><div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><small class="block text-[9px] uppercase text-zinc-500">Registros</small><strong class="text-xs">{{ number_format($restoreSummary['records'] ?? 0) }}</strong></div><div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><small class="block text-[9px] uppercase text-zinc-500">Archivos</small><strong class="text-xs">{{ number_format($restoreSummary['files'] ?? 0) }}</strong></div></div>
                <fieldset><legend class="mb-2 text-xs font-bold">Contenido a restaurar</legend><div class="grid gap-2 sm:grid-cols-3">@foreach($restoreModes as $mode)<label class="cursor-pointer"><input type="radio" wire:model="restoreMode" value="{{ $mode }}" class="peer sr-only"><span class="flex min-h-11 items-center justify-center rounded-xl border border-zinc-200 px-3 text-center text-xs font-bold peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-900 dark:border-zinc-700 dark:peer-checked:border-amber-400 dark:peer-checked:bg-amber-400/10 dark:peer-checked:text-amber-100">{{ $backupTypeLabel($mode) }}</span></label>@endforeach</div>@error('restoreMode')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror</fieldset>
                <label class="block"><span class="mb-1.5 block text-xs font-bold">Escribe <code>RESTAURAR</code> para confirmar</span><input wire:model="restoreConfirmation" autocomplete="off" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm uppercase dark:border-zinc-700" placeholder="RESTAURAR">@error('restoreConfirmation')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror</label>
            </div>
            <footer class="flex flex-col-reverse gap-2 border-t border-zinc-200 p-4 dark:border-zinc-800 sm:flex-row sm:justify-end"><button wire:click="closeRestoreModal" wire:loading.attr="disabled" wire:target="restoreBackup" class="agro-button-secondary">Cancelar</button><button wire:click="restoreBackup" wire:loading.attr="disabled" wire:target="restoreBackup" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-amber-500 disabled:opacity-60"><span wire:loading.remove wire:target="restoreBackup">Crear copia previa y restaurar</span><span wire:loading wire:target="restoreBackup">Restaurando...</span></button></footer>
        </section>
    </div>
@endif
