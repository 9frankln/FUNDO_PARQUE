<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

trait HasRecentRecord
{
    #[Locked]
    public ?array $recentRecord = null;

    public function mountHasRecentRecord(): void
    {
        $payload = session('ui_recent_record');
        $scope = $payload['scope'] ?? null;
        $scopes = $this->recentRecordScopes();

        if (! is_array($payload)
            || ! isset($scopes[$scope])
            || ! in_array($payload['action'] ?? null, ['created', 'updated'], true)
            || (int) ($payload['user_id'] ?? 0) !== (int) auth()->id()
            || (int) ($payload['fundo_id'] ?? 0) !== (int) session('fundo_id')
            || (int) ($payload['expires_at'] ?? 0) < now()->timestamp) {
            return;
        }

        $config = $scopes[$scope];
        $record = $config['model']::query()
            ->where('fundo_id', session('fundo_id'))
            ->find((int) ($payload['id'] ?? 0));
        if (! $record) {
            return;
        }

        $this->recentRecord = [
            'scope' => $scope,
            'id' => (int) $record->getKey(),
            'action' => $payload['action'],
        ];

        if (isset($config['tab']) && property_exists($this, 'tab')) {
            $this->tab = $config['tab'];
        }
    }

    public function clearRecentRecord(): void
    {
        $this->recentRecord = null;
    }

    public function isRecentRecord(string $scope, int|string $id): bool
    {
        return ($this->recentRecord['scope'] ?? null) === $scope
            && (int) ($this->recentRecord['id'] ?? 0) === (int) $id;
    }

    protected function pinRecent(Builder $query, string $scope): Builder
    {
        if (($this->recentRecord['scope'] ?? null) !== $scope) {
            return $query;
        }

        $qualifiedKey = $query->getModel()->qualifyColumn($query->getModel()->getKeyName());

        return $query->orderByRaw("CASE WHEN {$qualifiedKey} = ? THEN 0 ELSE 1 END", [
            (int) $this->recentRecord['id'],
        ]);
    }

    abstract protected function recentRecordScopes(): array;
}
