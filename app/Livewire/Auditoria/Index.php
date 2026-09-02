<?php

namespace App\Livewire\Auditoria;

use App\Models\AuditoriaLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasCsvExport;
use App\Support\PaginationOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, WithPagination;

    private const PER_PAGE_OPTIONS = PaginationOptions::PER_PAGE;

    #[Url]
    public string $search = '';

    #[Url]
    public string $module = 'all';

    #[Url]
    public string $event = 'all';

    #[Url]
    public string $user = 'all';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url(as: 'por_pagina', except: 25)]
    public int $perPage = 25;

    // Modal de borrado de registros
    public bool $showDeleteModal = false;

    public string $deleteMode = 'today';

    public string $deleteUserId = '';

    public string $deleteFrom = '';

    public string $deleteTo = '';

    public int $deleteDays = 30;

    public string $deletePassword = '';

    public function getPuedeBorrarRegistrosProperty(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $this->authorizePermission('auditoria', 'leer');
        // URLs con valores inválidos (ej. ?user=0) se descartan para no
        // romper la vista ni mostrar "sin resultados" por error.
        if ($this->user !== 'all' && (int) $this->user <= 0) {
            $this->user = 'all';
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'module', 'event', 'user', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'module', 'event', 'user', 'from', 'to');
        $this->module = 'all';
        $this->event = 'all';
        $this->user = 'all';
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $value = (int) $value;
        $this->perPage = in_array($value, self::PER_PAGE_OPTIONS, true) ? $value : 25;
        $this->resetPage();
    }

    public function exportar()
    {
        $this->authorizePermission('auditoria', 'exportar');

        $headers = ['Fecha y hora', 'Actor', 'Usuario afectado', 'Evento', 'Módulo', 'Detalle', 'Resultado', 'IP', 'Equipo', 'Ruta', 'Metadatos'];
        $rows = $this->logsQuery()->with(['usuario:id,name,username,email', 'usuarioObjetivo:id,name,username,email'])
            ->latest('created_at')
            ->cursor();

        return $this->streamCsv('auditoria', $headers, $rows, fn (AuditoriaLog $log) => [
            $log->created_at?->format('Y-m-d H:i:s'),
            $this->userLabel($log->usuario),
            $this->userLabel($log->usuarioObjetivo),
            $log->event ?: $log->accion,
            $log->modulo,
            $log->detalle,
            $log->result,
            $log->ip_address,
            $log->user_agent,
            $log->method.' '.$log->url,
            json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function openDeleteModal(): void
    {
        $this->authorizeFundoAdmin();
        $this->resetDeleteFields();
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->resetDeleteFields();
    }

    private function resetDeleteFields(): void
    {
        $this->deleteMode = 'today';
        $this->deleteUserId = '';
        $this->deleteFrom = '';
        $this->deleteTo = '';
        $this->deleteDays = 30;
        $this->deletePassword = '';
        $this->resetValidation([
            'deleteMode', 'deleteUserId', 'deleteFrom', 'deleteTo', 'deleteDays', 'deletePassword',
        ]);
    }

    public function deleteLogs(): void
    {
        $this->authorizeFundoAdmin();

        $this->validate([
            'deleteMode' => ['required', Rule::in(['user', 'today', 'week', 'period', 'days', 'all'])],
        ]);

        if ($this->deleteMode === 'user') {
            $this->validate(['deleteUserId' => ['required', 'integer', 'gt:0']], [
                'deleteUserId.required' => 'Selecciona un usuario.',
            ]);
        } elseif ($this->deleteMode === 'period') {
            $this->validate([
                'deleteFrom' => ['required', 'date'],
                'deleteTo' => ['required', 'date', 'after_or_equal:deleteFrom'],
            ], [
                'deleteFrom.required' => 'Indica la fecha de inicio.',
                'deleteTo.required' => 'Indica la fecha de fin.',
                'deleteTo.after_or_equal' => 'La fecha de fin no puede ser anterior al inicio.',
            ]);
        } elseif ($this->deleteMode === 'days') {
            $this->validate(['deleteDays' => ['required', 'integer', 'between:1,3650']], [
                'deleteDays.required' => 'Indica cuántos días hacia atrás.',
                'deleteDays.between' => 'Usa un valor entre 1 y 3650 días.',
            ]);
        }

        $deleted = $this->deleteLogsQuery()->delete();

        $metadata = ['modo' => $this->deleteMode];
        if ($this->deleteMode === 'user') {
            $metadata['usuario_id'] = (int) $this->deleteUserId;
        } elseif ($this->deleteMode === 'today') {
            $metadata['fecha'] = now()->toDateString();
        } elseif ($this->deleteMode === 'week') {
            $metadata['desde'] = now()->startOfWeek()->toDateString();
            $metadata['hasta'] = now()->endOfWeek()->toDateString();
        } elseif ($this->deleteMode === 'period') {
            $metadata['desde'] = $this->deleteFrom;
            $metadata['hasta'] = $this->deleteTo;
        } elseif ($this->deleteMode === 'days') {
            $metadata['dias'] = $this->deleteDays;
        }

        $this->closeDeleteModal();
        $this->resetPage();

        app(AuditLogger::class)->record(
            'auditoria.limpieza',
            'auditoria',
            "Eliminó {$deleted} registro(s) de auditoría.",
            metadata: $metadata,
        );

        $this->dispatch('swal:toast', [
            'title' => '¡Eliminado!',
            'text' => "Se eliminaron {$deleted} registro(s) de auditoría.",
            'icon' => 'success',
        ]);

        $this->dispatch('$refresh');
    }

    private function deleteLogsQuery(): Builder
    {
        $fundoId = $this->fundoId();

        $query = AuditoriaLog::query()
            ->where(function (Builder $scope) use ($fundoId): void {
                $scope->where('fundo_id', $fundoId)
                    ->orWhere(function (Builder $global) use ($fundoId): void {
                        $global->whereNull('fundo_id')
                            ->where(function (Builder $users) use ($fundoId): void {
                                $users->whereHas('usuario.fundos', fn (Builder $fundos) => $fundos->where('fundos.id', $fundoId))
                                    ->orWhereHas('usuarioObjetivo.fundos', fn (Builder $fundos) => $fundos->where('fundos.id', $fundoId));
                            });
                    });
            });

        if ($this->deleteMode === 'user') {
            $userId = (int) $this->deleteUserId;
            $query->where(fn (Builder $users) => $users->where('user_id', $userId)->orWhere('target_user_id', $userId));
        } elseif ($this->deleteMode === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($this->deleteMode === 'week') {
            $query->whereBetween('created_at', [
                now()->startOfWeek()->toDateTimeString(),
                now()->endOfWeek()->toDateTimeString(),
            ]);
        } elseif ($this->deleteMode === 'period') {
            $query->whereDate('created_at', '>=', $this->deleteFrom)
                ->whereDate('created_at', '<=', $this->deleteTo);
        } elseif ($this->deleteMode === 'days') {
            $query->whereDate('created_at', '>=', now()->subDays($this->deleteDays)->toDateString());
        }

        return $query;
    }

    private function currentUserIsFundoAdmin(): bool
    {
        auth()->user()?->loadMissing('fundos');
        $membership = auth()->user()?->fundos->firstWhere('id', $this->fundoId());

        return (bool) $membership?->pivot?->es_administrador;
    }

    private function authorizeFundoAdmin(): void
    {
        abort_unless(auth()->check(), 403, 'Debe estar autenticado.');
    }

    public function render()
    {
        $query = $this->logsQuery();
        $logs = (clone $query)
            ->with(['usuario:id,name,username,email', 'usuarioObjetivo:id,name,username,email'])
            ->latest('created_at')
            ->paginate($this->perPage);
        $fundoId = $this->fundoId();
        $users = User::query()
            ->whereHas('fundos', fn (Builder $query) => $query->where('fundos.id', $fundoId))
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email']);
        // Use unfiltered base query so dropdown options don't collapse
        // when a module/event filter is active.
        $baseQuery = $this->baseLogsQuery();
        $modules = (clone $baseQuery)
            ->whereNotNull('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');
        $events = (clone $baseQuery)
            ->selectRaw('COALESCE(event, accion) as audit_event')
            ->distinct()
            ->orderBy('audit_event')
            ->pluck('audit_event');

        return view('livewire.auditoria.index', [
            'logs' => $logs,
            'users' => $users,
            'modules' => $modules,
            'events' => $events,
            'perPageOptions' => array_combine(self::PER_PAGE_OPTIONS, self::PER_PAGE_OPTIONS),
        ])->layout('layouts.app');
    }

    /**
     * Base query scoped to the active fundo (no user filters applied).
     * Used for populating dropdown options without collapse.
     */
    private function baseLogsQuery(): Builder
    {
        $fundoId = $this->fundoId();

        return AuditoriaLog::query()
            ->where(function (Builder $query) use ($fundoId): void {
                $query->where('fundo_id', $fundoId)
                    ->orWhere(function (Builder $global) use ($fundoId): void {
                        $global->whereNull('fundo_id')
                            ->where(function (Builder $users) use ($fundoId): void {
                                $users->whereHas('usuario.fundos', fn (Builder $fundos) => $fundos->where('fundos.id', $fundoId))
                                    ->orWhereHas('usuarioObjetivo.fundos', fn (Builder $fundos) => $fundos->where('fundos.id', $fundoId));
                            });
                    });
            });
    }

    private function logsQuery(): Builder
    {
        return $this->baseLogsQuery()
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.trim($this->search).'%';
                $query->where(function (Builder $scope) use ($search): void {
                    $scope->where('accion', 'like', $search)
                        ->orWhere('event', 'like', $search)
                        ->orWhere('modulo', 'like', $search)
                        ->orWhere('detalle', 'like', $search)
                        ->orWhere('ip_address', 'like', $search)
                        ->orWhere('url', 'like', $search)
                        ->orWhereHas('usuario', fn (Builder $users) => $users->where('name', 'like', $search)->orWhere('username', 'like', $search)->orWhere('email', 'like', $search))
                        ->orWhereHas('usuarioObjetivo', fn (Builder $users) => $users->where('name', 'like', $search)->orWhere('username', 'like', $search)->orWhere('email', 'like', $search));
                });
            })
            ->when($this->module !== 'all', fn (Builder $query) => $query->where('modulo', $this->module))
            ->when($this->event !== 'all', fn (Builder $query) => $query->where(fn (Builder $event) => $event->where('event', $this->event)->orWhere('accion', $this->event)))
            ->when($this->user !== 'all', function (Builder $query): void {
                $userId = (int) $this->user;
                if ($userId <= 0) {
                    return;
                }
                $query->where(fn (Builder $users) => $users->where('user_id', $userId)->orWhere('target_user_id', $userId));
            })
            ->when($this->from !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->to));
    }

    private function fundoId(): int
    {
        $fundoId = (int) session('fundo_id');
        if (! $fundoId) {
            $fundoId = (int) auth()->user()?->fundos->first()?->id;
        }
        if (! $fundoId) {
            $fundoId = (int) auth()->user()?->fundoActivo()?->id;
        }
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        return $fundoId;
    }

    private function userLabel(?User $user): string
    {
        return $user ? trim($user->name.' · '.$user->username) : 'Sistema';
    }
}
