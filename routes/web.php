<?php

use App\Http\Controllers\DatabaseBackupDownloadController;
use App\Http\Controllers\PublicLandingController;
use App\Livewire\Admin\LandingManager;
use App\Livewire\Animal\Form;
use App\Livewire\Animal\Index;
use App\Livewire\Animal\Show;
use App\Livewire\Auditoria\Index as AuditoriaIndex;
use App\Livewire\Buscador;
use App\Livewire\Dashboard;
use App\Livewire\Engorde\LoteForm;
use App\Livewire\Finanzas\AsignacionForm;
use App\Livewire\Finanzas\AsignacionShow;
use App\Livewire\Finanzas\MovimientoForm;
use App\Livewire\Finanzas\MovimientoShow;
use App\Livewire\Monitoreo\PartoForm;
use App\Livewire\Monitoreo\ProfilaxisForm;
use App\Livewire\Monitoreo\SanidadForm;
use App\Models\AsignacionFamiliar;
use App\Models\Fundo;
use App\Models\Movimiento;
use App\Models\RegistroFoto;
use App\Services\AuditLogger;
use App\Services\Security\UserSessionService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', PublicLandingController::class)->name('home');

Route::middleware(['auth', 'verified', 'fundo', 'actividad'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // Animal routes
    Route::get('animal', Index::class)->middleware('permiso:animal.leer')->name('animal.index');
    Route::get('animal/nuevo', Form::class)->middleware('permiso:animal.crear')->name('animal.create');
    Route::get('animal/editar/{id}', Form::class)->middleware('permiso:animal.actualizar')->name('animal.edit');
    Route::get('animal/{id}', Show::class)->middleware('permiso:animal.leer')->name('animal.show');

    // Keep existing bookmarks working while users move to the new module URL.
    Route::redirect('ganado', '/animal', 301);
    Route::get('ganado/nuevo', fn () => redirect()->route('animal.create', status: 301));
    Route::get('ganado/editar/{id}', fn ($id) => redirect()->route('animal.edit', ['id' => $id], 301));
    Route::get('ganado/{id}', fn ($id) => redirect()->route('animal.show', ['id' => $id], 301));

    // Engorde routes
    Route::get('engorde', App\Livewire\Engorde\Index::class)->middleware('permiso:engorde.leer')->name('engorde.index');
    Route::get('engorde/lote/nuevo', LoteForm::class)->middleware('permiso:engorde.crear')->name('engorde.lote.create');
    Route::get('engorde/lote/editar/{id}', LoteForm::class)->middleware('permiso:engorde.actualizar')->name('engorde.lote.edit');
    Route::get('engorde/lote/{id}', App\Livewire\Engorde\Show::class)->middleware('permiso:engorde.leer')->name('engorde.lote.show');

    // Leche routes
    Route::get('leche', App\Livewire\Leche\Index::class)->middleware('permiso:leche.leer')->name('leche.index');
    Route::get('leche/nuevo', App\Livewire\Leche\Form::class)->middleware('permiso:leche.crear')->name('leche.create');
    Route::get('leche/editar/{id}', App\Livewire\Leche\Form::class)->middleware('permiso:leche.actualizar')->name('leche.edit');
    Route::get('leche/{id}', App\Livewire\Leche\Show::class)->middleware('permiso:leche.leer')->name('leche.show');

    // Queso routes
    Route::get('queso', App\Livewire\Queso\Index::class)->middleware('permiso:queso.leer')->name('queso.index');
    Route::get('queso/nuevo', App\Livewire\Queso\Form::class)->middleware('permiso:queso.crear')->name('queso.create');
    Route::get('queso/editar/{id}', App\Livewire\Queso\Form::class)->middleware('permiso:queso.actualizar')->name('queso.edit');
    Route::get('queso/{id}', App\Livewire\Queso\Show::class)->middleware('permiso:queso.leer')->name('queso.show');

    // Finanzas routes
    Route::get('finanzas', App\Livewire\Finanzas\Index::class)->middleware('permiso:finanzas.leer')->name('finanzas.index');
    Route::get('finanzas/movimiento/nuevo', MovimientoForm::class)->middleware('permiso:finanzas.crear')->name('finanzas.movimiento.create');
    Route::get('finanzas/movimiento/editar/{id}', MovimientoForm::class)->middleware('permiso:finanzas.actualizar')->name('finanzas.movimiento.edit');
    Route::get('finanzas/movimiento/{id}', MovimientoShow::class)->middleware('permiso:finanzas.leer')->name('finanzas.movimiento.show');
    Route::get('finanzas/asignacion/nueva', AsignacionForm::class)->middleware('permiso:finanzas.crear')->name('finanzas.asignacion.create');
    Route::get('finanzas/asignacion/editar/{id}', AsignacionForm::class)->middleware('permiso:finanzas.actualizar')->name('finanzas.asignacion.edit');
    Route::get('finanzas/asignacion/{id}', AsignacionShow::class)->middleware('permiso:finanzas.leer')->name('finanzas.asignacion.show');

    // Monitoreo routes
    Route::get('monitoreo', App\Livewire\Monitoreo\Index::class)->middleware('permiso:monitoreo.leer')->name('monitoreo.index');
    Route::get('monitoreo/sanidad/nueva', SanidadForm::class)->middleware('permiso:monitoreo.crear')->name('monitoreo.sanidad.create');
    Route::get('monitoreo/sanidad/editar/{id}', SanidadForm::class)->middleware('permiso:monitoreo.actualizar')->name('monitoreo.sanidad.edit');
    Route::get('monitoreo/profilaxis/nueva', ProfilaxisForm::class)->middleware('permiso:monitoreo.crear')->name('monitoreo.profilaxis.create');
    Route::get('monitoreo/profilaxis/editar/{id}', ProfilaxisForm::class)->middleware('permiso:monitoreo.actualizar')->name('monitoreo.profilaxis.edit');
    Route::get('monitoreo/parto/nuevo', PartoForm::class)->middleware('permiso:monitoreo.crear')->name('monitoreo.parto.create');
    Route::get('monitoreo/parto/editar/{id}', PartoForm::class)->middleware('permiso:monitoreo.actualizar')->name('monitoreo.parto.edit');
    Route::get('archivos/fotos-registro/{foto}', function (RegistroFoto $foto) {
        $record = $foto->fotografiable;
        abort_unless($record && (int) $record->fundo_id === (int) session('fundo_id'), 404);
        abort_unless(Storage::disk('local')->exists($foto->ruta), 404);

        return Storage::disk('local')->response($foto->ruta, headers: [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    })->middleware('permiso:monitoreo.leer')->name('record-photo.show');

    Route::get('archivos/comprobantes/{movimiento}', function (Movimiento $movimiento) {
        abort_unless((int) $movimiento->fundo_id === (int) session('fundo_id'), 404);
        abort_unless($movimiento->comprobante_ruta, 404);
        abort_unless(Storage::disk('local')->exists($movimiento->comprobante_ruta), 404);

        return Storage::disk('local')->response($movimiento->comprobante_ruta, headers: [
            // La URL estable del movimiento debe revalidarse tras reemplazar comprobante.
            'Cache-Control' => 'private, no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    })->middleware('permiso:finanzas.leer')->name('movimiento.comprobante');

    Route::get('archivos/asignaciones/{asignacion}/foto', function (AsignacionFamiliar $asignacion) {
        abort_unless((int) $asignacion->fundo_id === (int) session('fundo_id'), 404);
        abort_unless($asignacion->foto_ruta, 404);
        abort_unless(Storage::disk('local')->exists($asignacion->foto_ruta), 404);

        return Storage::disk('local')->response($asignacion->foto_ruta, headers: [
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    })->middleware('permiso:finanzas.leer')->name('asignacion.foto');

    // Buscador General route
    Route::get('buscador', Buscador::class)->middleware('permiso:buscador.leer')->name('buscador');

    // Ajustes routes
    Route::get('ajustes', App\Livewire\Ajustes\Index::class)->middleware('permiso:ajustes.leer')->name('ajustes.index');
    Route::get('auditoria', AuditoriaIndex::class)->middleware('permiso:auditoria.leer')->name('auditoria.index');
    Route::get('ajustes/web', LandingManager::class)->middleware('permiso:gestion_web.actualizar')->name('ajustes.web');
    Route::get('ajustes/backups/{backup}/download', DatabaseBackupDownloadController::class)
        ->middleware('permiso:ajustes.exportar')
        ->name('ajustes.backups.download');
});

Route::post('select-fundo/{fundo}', function (Fundo $fundo) {
    abort_unless(auth()->user()->fundos()
        ->where('fundos.id', $fundo->id)
        ->where('activo', true)
        ->exists(), 403);

    session(['fundo_id' => $fundo->id]);

    return redirect()->route('dashboard');
})->middleware('auth')->name('select-fundo');

Route::post('logout', function () {
    $user = auth()->user();
    if ($user) {
        app(UserSessionService::class)->revokeCurrent($user, request()->session()->getId(), $user, 'logout');
        app(AuditLogger::class)->record('sesion.cerrada', 'seguridad', 'Cierre de sesión.', $user, actor: $user);
    }
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::get('sin-fundo', function () {
    return 'No tienes fundos asignados. Contacta al administrador.';
})->middleware('auth')->name('sin-fundo');

Route::get('seleccionar-fundo', function () {
    $fundos = auth()->user()->fundos()->where('activo', true)->get();

    return view('auth.select-fundo', compact('fundos'));
})->middleware('auth')->name('seleccionar-fundo');

require __DIR__.'/auth.php';

Route::fallback(fn () => response()->view('errors.404', status: 404));
