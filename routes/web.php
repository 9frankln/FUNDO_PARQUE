<?php

use App\Http\Controllers\DatabaseBackupDownloadController;
use App\Http\Controllers\PdfPreviewController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\SecureFileController;
use App\Livewire\Admin\LandingManager;
use App\Livewire\Animal\Form;
use App\Livewire\Animal\Index;
use App\Livewire\Animal\Show;
use App\Livewire\Auditoria\Index as AuditoriaIndex;
use App\Livewire\Buscador;
use App\Livewire\Dashboard;
use App\Livewire\Engorde\LoteForm;
use App\Livewire\Finanzas\MovimientoForm;
use App\Livewire\Finanzas\MovimientoShow;
use App\Livewire\Monitoreo\PartoForm;
use App\Livewire\Monitoreo\SanidadForm;
use App\Models\Fundo;
use App\Services\AuditLogger;
use App\Services\Security\UserSessionService;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicLandingController::class)->name('home');

Route::middleware(['auth', 'fundo', 'actividad'])->group(function () {
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
    Route::get('finanzas/asignacion/nueva', fn () => redirect()->route('finanzas.movimiento.create'))->middleware('permiso:finanzas.crear')->name('finanzas.asignacion.create');

    // Monitoreo routes
    Route::get('monitoreo', App\Livewire\Monitoreo\Index::class)->middleware('permiso:monitoreo.leer')->name('monitoreo.index');
    Route::get('monitoreo/sanidad/nueva', SanidadForm::class)->middleware('permiso:monitoreo.crear')->name('monitoreo.sanidad.create');
    Route::get('monitoreo/sanidad/editar/{id}', SanidadForm::class)->middleware('permiso:monitoreo.actualizar')->name('monitoreo.sanidad.edit');
    Route::get('monitoreo/parto/nuevo', PartoForm::class)->middleware('permiso:monitoreo.crear')->name('monitoreo.parto.create');
    Route::get('monitoreo/parto/editar/{id}', PartoForm::class)->middleware('permiso:monitoreo.actualizar')->name('monitoreo.parto.edit');

    // Medicamentos e inventario sanitario
    Route::get('medicamentos', App\Livewire\Medicamentos\Index::class)->middleware('permiso:medicamentos.leer')->name('medicamentos.index');
    Route::get('medicamentos/nuevo', App\Livewire\Medicamentos\Form::class)->middleware('permiso:medicamentos.crear')->name('medicamentos.create');
    Route::get('medicamentos/editar/{id}', App\Livewire\Medicamentos\Form::class)->middleware('permiso:medicamentos.actualizar')->name('medicamentos.edit');
    Route::get('medicamentos/{medicamento}/ingreso', App\Livewire\Medicamentos\LoteForm::class)->middleware('permiso:medicamentos.crear')->name('medicamentos.lote.create');
    Route::get('medicamentos/{medicamento}/movimiento', App\Livewire\Medicamentos\MovimientoForm::class)->middleware('permiso:medicamentos.actualizar')->name('medicamentos.movimiento.create');
    Route::get('medicamentos/{id}', App\Livewire\Medicamentos\Show::class)->middleware('permiso:medicamentos.leer')->name('medicamentos.show');

    // Insumos y Materiales de Botiquín
    Route::get('insumos', App\Livewire\Insumos\Index::class)->middleware('permiso:medicamentos.leer')->name('insumos.index');
    Route::get('insumos/nuevo', App\Livewire\Insumos\Form::class)->middleware('permiso:medicamentos.crear')->name('insumos.create');
    Route::get('insumos/editar/{id}', App\Livewire\Insumos\Form::class)->middleware('permiso:medicamentos.actualizar')->name('insumos.edit');
    Route::get('insumos/{id}', App\Livewire\Insumos\Show::class)->middleware('permiso:medicamentos.leer')->name('insumos.show');
    Route::get('archivos/fotos-registro/{foto}', [SecureFileController::class, 'showRegistroFoto'])
        ->middleware('permiso:monitoreo.leer')
        ->name('record-photo.show');

    Route::get('archivos/comprobantes/{movimiento}', [SecureFileController::class, 'showComprobante'])
        ->middleware('permiso:finanzas.leer')
        ->name('movimiento.comprobante');

    // Buscador General route
    Route::get('buscador', Buscador::class)->middleware('permiso:buscador.leer')->name('buscador');

    // Ajustes routes
    Route::get('ajustes', App\Livewire\Ajustes\Index::class)->middleware('permiso:ajustes.leer')->name('ajustes.index');
    Route::get('auditoria', AuditoriaIndex::class)->middleware('permiso:auditoria.leer')->name('auditoria.index');
    Route::get('ajustes/web', LandingManager::class)->middleware('permiso:gestion_web.actualizar')->name('ajustes.web');
    Route::get('ajustes/backups/{backup}/download', DatabaseBackupDownloadController::class)
        ->middleware('permiso:ajustes.exportar')
        ->name('ajustes.backups.download');

    // PDF preview endpoint — serves cached PDFs by token (no base64 in Livewire state)
    Route::get('pdf-preview/{token}', PdfPreviewController::class)->name('pdf.preview');
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

Route::view('sin-fundo', 'errors.sin-fundo')->middleware('auth')->name('sin-fundo');

Route::get('seleccionar-fundo', function () {
    $fundos = auth()->user()->fundos()->where('activo', true)->get();

    return view('auth.select-fundo', compact('fundos'));
})->middleware('auth')->name('seleccionar-fundo');

require __DIR__.'/auth.php';

Route::fallback(fn () => response()->view('errors.404', status: 404));
