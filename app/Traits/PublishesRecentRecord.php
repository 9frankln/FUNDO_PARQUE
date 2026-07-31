<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait PublishesRecentRecord
{
    protected function publishRecentRecord(string $scope, Model $record): void
    {
        session()->flash('ui_recent_record', [
            'scope' => $scope,
            'id' => (int) $record->getKey(),
            'action' => $record->wasRecentlyCreated ? 'created' : 'updated',
            'fundo_id' => (int) $record->fundo_id,
            'user_id' => (int) auth()->id(),
            'expires_at' => now()->addMinute()->timestamp,
        ]);
    }
}
