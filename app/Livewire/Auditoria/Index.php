<?php

namespace App\Livewire\Auditoria;

use App\Models\AuditoriaLog;
use App\Models\User;
use App\Traits\AuthorizesPermissions;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, WithPagination;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

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

    public function mount(): void
    {
        $this->authorizePermission('auditoria', 'leer');
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

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Fecha y hora', 'Actor', 'Usuario afectado', 'Evento', 'Módulo', 'Detalle', 'Resultado', 'IP', 'Equipo', 'Ruta', 'Metadatos']);

            $this->logsQuery()->with(['usuario:id,name,username,email', 'usuarioObjetivo:id,name,username,email'])
                ->latest('created_at')
                ->cursor()
                ->each(function (AuditoriaLog $log) use ($output): void {
                    fputcsv($output, [
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
                });

            fclose($output);
        }, 'auditoria_'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
        $modules = (clone $this->logsQuery())
            ->whereNotNull('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');
        $events = (clone $this->logsQuery())
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

    private function logsQuery(): Builder
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
            })
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
                $query->where(fn (Builder $users) => $users->where('user_id', $userId)->orWhere('target_user_id', $userId));
            })
            ->when($this->from !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->to));
    }

    private function fundoId(): int
    {
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        return $fundoId;
    }

    private function userLabel(?User $user): string
    {
        return $user ? trim($user->name.' · '.$user->username) : 'Sistema';
    }
}
