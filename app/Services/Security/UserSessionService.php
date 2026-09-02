<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserSessionService
{
    public function claim(User $user, string $sessionId, ?Request $request = null): bool
    {
        if ($sessionId === '') {
            return true;
        }

        return DB::transaction(function () use ($user, $sessionId, $request): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $this->expireInactiveSessions($lockedUser);
            $this->syncNativeSessions($lockedUser);
            $sessionHash = $this->hash($sessionId);
            $existing = UserSession::query()->where('session_hash', $sessionHash)->first();

            if ($existing) {
                if ($existing->revoked_at) {
                    return false;
                }

                $this->touchRecord($existing, $request);

                return true;
            }

            if ($this->activeFor($lockedUser)->count() >= $this->limitFor($lockedUser)) {
                return false;
            }

            UserSession::create([
                'user_id' => $lockedUser->id,
                'session_hash' => $sessionHash,
                'session_id' => $sessionId,
                'device_label' => $this->deviceLabel($request),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'last_activity_at' => now(),
            ]);

            return true;
        });
    }

    public function touch(User $user, string $sessionId, ?Request $request = null): void
    {
        if ($sessionId === '') {
            return;
        }

        $record = UserSession::query()
            ->where('user_id', $user->id)
            ->where('session_hash', $this->hash($sessionId))
            ->whereNull('revoked_at')
            ->first();

        if ($record) {
            $this->touchRecord($record, $request);
        }
    }

    public function currentSessionAllowed(User $user, string $sessionId): bool
    {
        if ($sessionId === '') {
            return true;
        }

        $record = UserSession::query()
            ->where('user_id', $user->id)
            ->where('session_hash', $this->hash($sessionId))
            ->first();

        if (! $record) {
            return true;
        }

        if ($record->revoked_at) {
            return false;
        }

        if (! $record->last_activity_at?->gte(now()->subMinutes($this->idleTimeoutFor($user) + 2))) {
            $this->revoke($record, null, 'expired');

            return false;
        }

        return true;
    }

    public function activeFor(User $user): Collection
    {
        return UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('last_activity_at', '>=', now()->subMinutes($this->idleTimeoutFor($user)))
            ->oldest('last_activity_at')
            ->get();
    }

    public function revoke(UserSession $session, ?User $actor = null, string $reason = 'administrator'): void
    {
        if ($session->revoked_at) {
            return;
        }

        $session->forceFill([
            'revoked_at' => now(),
            'revoked_by' => $actor?->id,
            'revocation_reason' => $reason,
        ])->save();

        $this->deleteNativeSession($session->session_id);
    }

    public function revokeAll(User $user, ?User $actor = null, string $reason = 'administrator'): int
    {
        $sessions = UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->oldest('last_activity_at')
            ->get();
        foreach ($sessions as $session) {
            $this->revoke($session, $actor, $reason);
        }

        $nativeCount = 0;
        if (config('session.driver') === 'database') {
            $nativeCount = DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        return max($sessions->count(), $nativeCount);
    }

    public function enforceLimit(User $user, ?User $actor = null): int
    {
        $this->expireInactiveSessions($user);
        $sessions = $this->activeFor($user);
        $excess = $sessions->count() - $this->limitFor($user);

        if ($excess <= 0) {
            return 0;
        }

        $sessions->take($excess)->each(fn (UserSession $session) => $this->revoke($session, $actor, 'limit_reduced'));

        return $excess;
    }

    public function revokeCurrent(User $user, string $sessionId, ?User $actor = null, string $reason = 'account_inactive'): void
    {
        if ($sessionId === '') {
            return;
        }

        $session = UserSession::query()
            ->where('user_id', $user->id)
            ->where('session_hash', $this->hash($sessionId))
            ->first();

        if ($session) {
            $this->revoke($session, $actor, $reason);
        }
    }

    public function hash(string $sessionId): string
    {
        return hash('sha256', $sessionId);
    }

    public function idleTimeoutFor(User $user): int
    {
        $isAdmin = $user->esAdministrador();

        // Sesión sin límite: administrador con valor null → nunca cierra por inactividad.
        if ($isAdmin && $user->session_idle_timeout_minutes === null) {
            return 525600;
        }

        $maximum = max(5, (int) config('session.lifetime', 30));

        // Administrador: respeta el tiempo programado (5 min – 1 año).
        if ($isAdmin) {
            return min(525600, max(5, (int) $user->session_idle_timeout_minutes));
        }

        // Usuario estándar: tope = lifetime de la sesión (config).
        return min($maximum, max(5, (int) ($user->session_idle_timeout_minutes ?: $maximum)));
    }

    private function expireInactiveSessions(User $user): void
    {
        UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('last_activity_at', '<', now()->subMinutes($this->idleTimeoutFor($user)))
            ->update([
                'revoked_at' => now(),
                'revocation_reason' => 'expired',
                'updated_at' => now(),
            ]);
    }

    private function syncNativeSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $rows = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('last_activity', '>=', now()->subMinutes($this->idleTimeoutFor($user))->timestamp)
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);

        foreach ($rows as $row) {
            $hash = $this->hash($row->id);
            if (UserSession::query()->where('session_hash', $hash)->exists()) {
                continue;
            }

            UserSession::create([
                'user_id' => $user->id,
                'session_hash' => $hash,
                'session_id' => $row->id,
                'device_label' => 'Sesión existente',
                'ip_address' => $row->ip_address,
                'user_agent' => $row->user_agent,
                'last_activity_at' => now()->setTimestamp((int) $row->last_activity),
            ]);
        }
    }

    private function touchRecord(UserSession $session, ?Request $request): void
    {
        $session->forceFill([
            'last_activity_at' => now(),
            'ip_address' => $request?->ip() ?? $session->ip_address,
            'user_agent' => $request?->userAgent() ?? $session->user_agent,
            'device_label' => $this->deviceLabel($request) ?? $session->device_label,
        ])->save();
    }

    private function deleteNativeSession(string $sessionId): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('id', $sessionId)->delete();
        }
    }

    private function limitFor(User $user): int
    {
        if ($user->esAdministrador() || (int) $user->max_active_sessions === 0) {
            return PHP_INT_MAX;
        }

        return max(1, min(10, (int) $user->max_active_sessions));
    }

    private function deviceLabel(?Request $request): ?string
    {
        $agent = strtolower((string) $request?->userAgent());
        if ($agent === '') {
            return null;
        }

        $platform = match (true) {
            str_contains($agent, 'iphone') => 'iPhone',
            str_contains($agent, 'ipad') => 'iPad',
            str_contains($agent, 'android') => 'Android',
            str_contains($agent, 'windows') => 'Windows',
            str_contains($agent, 'mac os') => 'Mac',
            str_contains($agent, 'linux') => 'Linux',
            default => 'Equipo desconocido',
        };
        $browser = match (true) {
            str_contains($agent, 'edg/') => 'Edge',
            str_contains($agent, 'firefox/') => 'Firefox',
            str_contains($agent, 'chrome/') => 'Chrome',
            str_contains($agent, 'safari/') => 'Safari',
            default => 'Navegador',
        };

        return $browser.' · '.$platform;
    }
}
