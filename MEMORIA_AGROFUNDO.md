# 🧠 MEMORIA COMPLETA DE AGROFUNDO — Exportación para otro modelo

> **Cómo usar:** copia/pega este archivo como contexto en otro modelo (o indícale que lo lea). Contiene toda la memoria del proyecto AgroFundo.
> **Ubicación física original de la memoria:**
> - Repo: `C:\Users\madar\AppData\Roaming\Code\User\workspaceStorage\9201736412a440c425d86cdc2ebbac00\GitHub.copilot-chat\memory-tool\memories\repo\agrofundo.md`
> - Usuario (global): `C:\Users\madar\AppData\Roaming\Code\User\globalStorage\github.copilot-chat\memory-tool\memories\{preferencias,seguridad-dependencias}.md`
> - Exportado: 2026-08-09

---

# PARTE 1 — MEMORIA DEL REPOSITORIO (agrofundo.md)

# AgroFundo — Contexto del proyecto

## Stack
- PHP 8.3, Laravel ^13.8, Livewire 3.6 + Volt 1.7, Tailwind 3, Vite 8
- MySQL 8 prod & dev (`fundo_parque01`), MySQL aislado para tests (`fundo_parque_testing`)
- Paquetes clave: maatwebsite/excel (XLSX), barryvdh/laravel-dompdf (PDF), spatie/laravel-medialibrary (solo LandingBlock), sweetalert2 (único JS runtime)

## Arquitectura
- Multi-fundo (multi-tenancy): `fundo_id` en sesión, scope global `FundoScope` + trait `BelongsToFundo`
- 1 clase Livewire por página, patrón `Index`/`Form`/`Show` por módulo
- Traits transversales: `Auditable`, `BelongsToFundo`, `AuthorizesPermissions`, `HandlesRecordPhotos`, `HasRecentRecord`
- Services: `AuditLogger`, `AnimalInventoryService`, `FundoProvisioner`, `FundoDatabaseBackupService`, `FundoContext`, `UserSessionService`
- Support: `AnimalCodeAllocator`, `LoteCodeAllocator`, `BrandPalette`, `SystemBranding`, `ImageFrame`, `ImageOptimizer` (webp ≤1.5MB), `EngordeReport`
- Middleware: `CheckPermission`, `EnsureFundoSelected`, `RecordActivity`, `EnsureActiveAccountSession`, `SecurityHeaders`

## Módulos (app/Livewire/)
Animal, Engorde, Leche, Queso, Finanzas, Monitoreo (sanidad+casos clínicos unificados, partos, alertas), Ajustes (traits en Ajustes/Traits/), Auditoria, Admin (landing), Buscador, Dashboard

## UNIFICACIÓN PREVENCIÓN→CASO CLÍNICO (2026-08-08) — suite completa verde
- Usuario pidió: integrar Prevención (profilaxis) DENTRO de Caso Clínico, un solo concepto, un solo nombre, sin tabs separados, sin PDFs separados, CRUD completo (ver/editar/eliminar), alertas simples y eficientes.
- MIGRACIÓN `2026_08_08_000000_unify_prevention_into_sanidad.php` (REESCRITA IDEMPOTENTE): elimina tablas `profilaxis_*`, añade a `sanidad_registros` columnas `tipo_evento` (clinico|preventivo), `alcance`, `tipo_intervencion`, `producto_marca`, `proposito`, `responsable`, `proxima_dosis`. Migra datos con `DB::table()` (NO modelos Eloquent — los modelos se eliminan y RefreshDatabase ejecuta TODAS las migraciones en tests). `up()` retorna si `!Schema::hasTable('profilaxis_registros')`. down() no-op.
- LECCIÓN CRÍTICA MIGRACIONES: si una migración usa modelos Eloquent que luego se ELIMINAN del código, los tests con RefreshDatabase fallan "Class not found" (ejecuta todas las migraciones). Usar SIEMPRE `DB::table()` + `Schema::hasTable/hasColumn` (idempotente) en migraciones que toquen tablas que luego se eliminan.
- LECCIÓN fillable + migración: la migración crea columnas PERO el modelo `SanidadRegistro` DEBE incluir esos campos en `$fillable`, si no Eloquent los descarta silenciosamente por mass-assignment protection (los registros se guardan con NULL/clinico). Al migrar datos con el modelo, verificar fillable.
- MODELOS ELIMINADOS: `ProfilaxisRegistro`, `ProfilaxisDosisProgramada`, `ProfilaxisForm.php`, `profilaxis-form.blade.php`. Después de eliminarlos correr `composer dump-autoload` (classmap).
- `AlertaProgramada`: eliminados fillable `profilaxis_dosis_id` y relación `dosisProfilaxis()`. Alertas preventivas quedan con `tipo='preventivo'`.
- `Monitoreo/Index`: ELIMINADO tab 'profilaxis' + filtros + `marcarDosisProfilaxisAplicada` + `deleteProfilaxis` + PDF section. AÑADIDO: filtro `sanidadTipoEvento` (clinico|preventivo), modal `openVerCasoModal` (CRUD ver), tabla unificada con columna Tipo + detalle condicional. `recentRecordScopes` solo sanidad/partos.
- `SanidadForm`: `mount($id=null, $tipo=null)` con `tipoEvento`; selector tipo (solo en create); campos preventivos condicionales (alcance/tipoIntervencion/productoMarca/proposito/responsable/proximaDosis); `proxima_dosis` se deriva de la primera dosis pendiente del plan; cuarentena solo para clínicos. Ruta `monitoreo.sanidad.create?tipo=preventivo` para crear preventivo.
- PDFs: `pdf/monitoreo.blade.php` y `pdf/animal.blade.php` sin secciones de profilaxis/prevención (unificadas en clínicas con columna "Tipo de evento").
- `Animal/Show`: timeline unifica clínicos+preventivos (mismo `sanidadRegistros`, `tipo_evento`); REPORT_SECTIONS sin 'preventive'. `render()` RE-CARGA relaciones con `$this->animal->load([...])` — Livewire pierde relaciones de 2º nivel al re-hidratar props Eloquent tras acciones (lazy load → 500 con preventLazyLoading).
- BackupService: `tableQueries` incluye `tratamiento_dosis`, sin tablas profilaxis; integridad sin checks de profilaxis.
- Tests: `ProfilaxisFormTest` → `PreventivoSanidadFormTest` (SanidadForm con tipo preventivo). Actualizados AnimalDetailedReportTest, PrivateFileAccessTest, UppercasePersistenceTest (campos preventivos ahora Lowercase en SanidadRegistro), MonitoreoReportAndThemeTest (anchos/columnas).
- BUG blade encontrado (pre-existente): `movimiento-form.blade.php` línea ~174 tenía `@error('proposito') ... @error` (faltaba `@enderror`) → "unexpected end of file" en tests. Al ver `view:cache` compila pero los tests fallan. Verificar siempre balance @error/@enderror.
- TESTS PRE-EXISTENTES rotos (NO por esta tarea): la migración `2026_08_08_000000_add_beneficiario_proposito_to_movimientos` (Asignación Familiar, misma sesión) inserta la categoría 14 "Asignación Familiar" → SeederSecurityTest 13→14, SearchAndCatalogTest (categoría global extra), FinanzasModuleTest y RecentRecordModulesTest (las asignaciones ahora son MOVIMIENTOS con beneficiario/proposito vía MovimientoForm — se actualizaron los tests al nuevo diseño).

## INCIDENTE BD VACÍA (2026-08-08) — RESTAURACIÓN COMPLETA
- Síntoma: usuario no podía acceder (admin/123456789 → "se borró"). Causa: BD `fundo_parque01` quedó con SOLO estructura (0 users, 0 fundos, 0 animales) — el restore previo del dump pre-unificación no dejó datos (probable `migrate:fresh` posterior o pipe fallido).
- SOLUCIÓN APLICADA: (1) `mysql -u root -e "DROP DATABASE IF EXISTS fundo_parque01; CREATE DATABASE fundo_parque01 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`; (2) `cmd /c "mysql -u root fundo_parque01 < storage\app\backup_pre_unificacion_20260808_053020.sql"` (dump estándar con DROP TABLE IF EXISTS, restaura estructura ANTIGUA con profilaxis + datos); (3) `php artisan migrate --force` → re-aplica la migración de unificación (idempotente) → migra 3 profilaxis→sanidad, elimina tablas profilaxis, queda migrations=50.
- RESULTADO VERIFICADO: users=2 (admin/123456789 hash verificado OK, pepe), fundos=1, animales=17, sanidad_registros=16 (7 clínico + 9 preventivo), tratamiento_dosis=19, profilaxis eliminada. Dashboard y /monitoreo cargan con datos.
- LECCIÓN: los SEEDERS NO crean usuarios (DatabaseSeeder solo CoreDataSeeder: roles/permisos, especies, razas, categorías, medicamentos). El usuario admin se crea MANUALMENTE (tinker/registro) → `migrate:fresh --seed` NO lo recupera. Backups restaurables: `storage/app/backup_pre_unificacion_20260808_053020.sql` (más reciente, 285KB, pre-unificación con datos).
- CREDENCIALES MySQL: root SIN contraseña. En PowerShell usar `cmd /c "mysql -u root < archivo"` para redirigir entrada (PS 5.1 no soporta `<`).

## BUGS ENCONTRADOS EN VERIFICACIÓN NAVEGADOR (2026-08-08, post-restauración)
1. **Lazy load en modal Ver Caso (Monitoreo/Index `openVerCasoModal`)**: eager load era `['animal','medicamento','fotos','dosisPlan']` pero el closure accedía a `$d->medicamento?->nombre` → `LazyLoadingViolationException` (preventLazyLoading activo) → 500 al abrir "Ver detalle". FIX: `'dosisPlan.medicamento'`. SIEMPRE eager-load relaciones usadas dentro de closures/map en Livewire.
2. **Query string en SanidadForm `mount($id=null, $tipo=null)`**: Livewire `mount` SOLO recibe parámetros de RUTA, NO query strings. El enlace `route('monitoreo.sanidad.create')?tipo=preventivo` no activaba el modo preventivo (mostraba formulario clínico). FIX: `if (! in_array($tipo, ['clinico','preventivo'], true)) { $tipo = request()->query('tipo'); }` antes de setear `$this->tipoEvento`. El test PreventivoSanidadFormTest pasaba porque pasa `['tipo' => 'preventivo']` como parámetro de montaje.
- VERIFICADO EN NAVEGADOR: tabs (Casos Clínicos/Reproducción/Alertas), tabla unificada con columna Tipo + badges + columnas condicionales, modal Ver Caso (preventivo y clínico), crear/editar preventivo (`?tipo=preventivo`), timeline animal con preventivos unificados. Botones "Ver detalle" de x-table-action usan `aria-label`/`title` (sin texto visible) — para clics en Playwright usar `button[aria-label="Ver detalle"]`.
- Tests de depuración `DebugRecent4Test.php` y `DebugRecent5Test.php` ELIMINADOS (obsoletos, referenciaban AsignacionForm/viewData('asignaciones')). Suite final: 208/208 verde.

## FIX LOGIN: campos consistentes claro/oscuro (2026-08-08)
- Síntoma: en MODO OSCURO los inputs del modal de acceso se veían BLANCOS (el usuario: "si está en modo oscuro debería también estar en ese modo"). Causa real: CSS compilado cacheado en el navegador (build viejo). El CSS actual `dark:bg-emerald-950/50` SÍ produce `rgba(4,47,46,.5)` (verificado con getComputedStyle).
- FIX ROBUSTO en `resources/views/welcome.blade.php` (CSS INLINE en <style>, junto a `.landing-login`): reglas explícitas `.landing-login input` (light: #fff / texto #022c22) y `.dark .landing-login input` (dark: rgba(4,47,46,.5) / texto #ecfdf5), más placeholders y autofill por tema. El CSS inline se sirve con el HTML → NO depende del caché de assets ni del purge de Tailwind.
- `app.css`: reglas globales anti-autofill `input:-webkit-autofill` (light #fff, dark #06261d). Después de tocar app.css correr `npm run build`.
- VERIFICADO: dark → ambos inputs rgba(4,47,46,0.5) con texto rgb(236,253,245); light → rgb(255,255,255). El toggle de tema pone `.dark` en `<html>` (darkMode: 'class' en tailwind.config.js, content incluye resources/views/**).
- LECCIÓN navegador: al probar con Playwright, un cambio de `.dark` debe medirse con getComputedStyle ANTES de restaurar el tema (el orden de evaluación importa).

## DISEÑO DROPDOWN ASIGNACIÓN FAMILIAR + FIX BLADE (2026-08-08)
- Usuario: "corrige el diseño de esa imagen, coloca la imagen 2 a otro color para que se vea que al presionar ahí se despliega ese estilo". Imágenes: (1) panel "Datos de asignación familiar" violeta, (2) opción "Asignación Familiar" seleccionada con checkmark, (3) dropdown "Categoría *".
- Objetivo: la opción "Asignación Familiar" en el dropdown de categoría del movimiento se distingue en COLOR VIOLETA (conecta con el panel violeta que se despliega al elegirla), en vez del teal genérico.
- IMPLEMENTADO:
  - `components/filter-select.blade.php`: props nuevos `specialValues` (array) y `specialTone` (default 'violet'). En x-data `specialValues`. En listbox: opción seleccionada+especial → `$specialPalette['selected']` (violeta); opción especial no seleccionada → `text-violet-700 dark:text-violet-300` + ícono personas; trigger con valor especial → texto violeta + ícono personas. NO intrusivo (sin props, igual que antes).
  - `app/Livewire/Finanzas/MovimientoForm.php`: getter `getAsignacionCategoriaIdProperty()` → id de la categoría cuyo nombre contiene 'asignaci'/'familiar' (de `$this->categorias`).
  - `movimiento-form.blade.php`: `<x-filter-select>` de categoría pasa `:special-values="$this->asignacionCategoriaId ? [(string) $this->asignacionCategoriaId] : []" special-tone="violet"`.
- FIX ERROR BLADE (500 en /finanzas/movimiento/nuevo): `movimiento-form.blade.php` había quedado con "syntax error, unexpected end of file, expecting elseif/else/endif" tras edición externa; el archivo actual compila y carga. Al tocar blades correr `php artisan view:clear`.
- VERIFICADO: opción Asignación Familiar violeta en lista y al seleccionarla (`bg-violet-600 text-white font-bold`), formulario violeta desplegado, /finanzas sin errores.

## REVISIÓN MÓDULO FINANZAS "que tenga sentido con los datos" (2026-08-08)
- Usuario pidió revisar /finanzas por errores y arreglar "cosas sin sentido" (filtros, dashboard, PDF, tabla con CRUD).
- BUG DE SENTIDO CORREGIDO: `dashboardData()` de `app/Livewire/Finanzas/Index.php` calculaba "Asignación familiar" y "Destinos familiares" desde la tabla `asignaciones_familiares` (vacía tras la unificación) → siempre S/.0.00 y "Sin asignaciones". FIX: calcular desde los MOVIMIENTOS con categoría Asignación Familiar (egreso + `esCategoriaAsignacionFamiliar()` por nombre contiene 'asignaci'/'familiar'). `dashboardData()` ya no consulta AsignacionFamiliar; usa `$monthMovements->where('tipo','egreso')->filter(...)`. Helper nuevo `esCategoriaAsignacionFamiliar(string): bool`.
- VERIFICADO (navegador): /finanzas carga SIN errores de consola; dashboard Ingresos S/.5 / Egresos S/.175 / Balance -170 / Asignación familiar 0 (ahora desde movimientos reales); tabla con CRUD completo (Fecha, Categoría, Detalle/Beneficiario, Comprobante, Monto, Ver/Editar/Eliminar); vista detalle MovimientoShow OK; modal Exportar PDF OK (secciones Resumen/Movimientos/Categorías); filtros completos (periodo, tipo, categoría, comprobante, montos, búsqueda). Log limpio (el único error 500 era el blade desbalanceado de 17:49 ya corregido).
- NOTA legacy: el tab 'asignaciones' del componente (queryString `?tab=asignaciones`) y AsignacionForm/AsignacionShow quedan como código latente (el blade index no muestra tabs; las asignaciones ya son movimientos). No se eliminaron para no romper tests/referencias.

## ESPACIADO FORMULARIO MOVIMIENTO (2026-08-08) — "Monto muy pegado, sin espacios en bordes"
- Usuario: "en la parte de monto está muy pegada, no tiene espacios, en los bordes, hazlo bien".
- CAMBIOS en `resources/views/livewire/finanzas/movimiento-form.blade.php`:
  - Sección datos: `p-5 sm:p-6 lg:p-7` → `p-6 sm:p-7 lg:p-8` (más aire en bordes).
  - Grid principal: `gap-4` → `gap-x-5 gap-y-5` (20px entre campos; antes 16px). OJO: el grid del panel violeta (Beneficiario/Propósito) queda con gap-4 (interno).
  - Panel violeta asignación: `p-4.5` → `p-5` (p-4.5 no es escala estándar Tailwind).
  - Input Monto: `py-2.5 pl-11 pr-3.5` → `py-3 pl-12 pr-4`; labels Monto/Fecha `mb-1`→`mb-1.5`.
  - Input Beneficiario: `px-3.5 py-2.5` → `px-4 py-3` (consistente con Monto).
  - Descripción: `rows 2.5`→`rows 3`, `py-3`, `mt-1`, y se sacó `sm:col-span-2` (estaba fuera del grid).
- VERIFICADO (getBoundingClientRect): gap Monto↔Fecha 20px, gap panel violeta→Monto 20px, input Monto padding-top 12px. Tests finanzas pasan.
- LECCIÓN: al editar blades con `multi_replace_string_in_file`, verificar SIEMPRE que cada reemplazo se aplicó (uno falló silenciosamente por "multiple matches" y otro por contexto genérico); usar contexto único.

## PDFs: QUITAR bloque "Administrador(es)" (NO PARA PDF) + footer + enlace animal en venta (2026-08-08)
- USUARIO pidió: (1) mejorar el PDF de finanzas, quitar el texto final "Incluye únicamente la información esencial seleccionada." (no profesional); (2) quitar el dato de "Administración/Administrador(es)" (los nombres de los admins, ej. "FRANKLIN CHOQUENAIRA QUISPE, PEPE") de TODOS los PDFs — "NO PARA PDF", recordarlo para los demás; (3) que el código del animal en el detalle de VENTA lleve a la ficha (ej. BOV26-006 → /animal/9).
- QUITADO el bloque "Administración/Administrador(es)" de los 9 PDFs: animal, animales, engorde, engorde-detallado, monitoreo, ordenos, queso, finance-index, finance-record (se mantiene "Generado por"). Los que pasan `$administrators` al PDF lo siguen pasando (inofensivo).
- finance-index.blade.php: footer → "{{ $branding->name }} · Reporte de finanzas · fecha" (sin "Incluye únicamente..."), header con `border-radius` + gradiente suave, footer `display:flex; justify-content:space-between`.
- TEST actualizado: AnimalDetailedReportTest línea 69 `assertStringContainsString('Administrador(es)', $html)` → `assertStringNotContainsString`.
- ENLACE ANIMAL EN VENTA: en `app/Livewire/Finanzas/MovimientoShow.php` `movement()` ahora hace `->with(['categoria','animalesVendidos'])` (relación `Movimiento::animalesVendidos()` = hasMany Animal por `movimiento_venta_id`). En `movimiento-show.blade.php` se agregó la sección "Animales vendidos" con tarjetas clicables (`route('animal.show', $id)`) mostrando foto (`asset('storage/'.$foto_ruta)`), arete y nombre. Verificado en /finanzas/movimiento/7 (BOV26-006 → /animal/9).

## INCIDENTE SOFT-DELETE ACCIDENTAL (2026-08-08) — restaurar sanidad
- Durante la verificación en navegador, clics con refs obsoletos de accesibilidad cayeron en botones "Eliminar" → 11 sanidad_registros (ids 5,8,9-17) soft-deleted a las 15:47-15:49. Los contadores de /monitoreo mostraban 0.
- FIX: `DB::table('sanidad_registros')->whereBetween('deleted_at', ['2026-08-08 15:47:00','2026-08-08 15:50:00'])->update(['deleted_at'=>null])` → 11 restaurados (quedan 11 activos; ids 1,2,3,4,6 ya estaban eliminados desde el backup).
- LECCIÓN: en verificación con navegador usar SIEMPRE selectores robustos (`button[aria-label="Ver detalle"]`, `locator().first()`) en lugar de refs de accesibilidad que quedan obsoletos entre snapshots y pueden disparar acciones destructivas.

## MOJIBAKE UTF-8→CP437 en TODA la BD (2026-08-08) — CORREGIDO + PREVENCIÓN
- SÍNTOMA: la landing mostraba "Gesti├│n rural", "Ganader├¡a", "acompa├▒a", "Jun├¡n" (caracteres de caja CP437 en vez de acentos).
- CAUSA RAIZ: el dump manual `backup_pre_unificacion_20260808_053020.sql` se GENERÓ con redirección de consola PowerShell (`mysqldump > archivo.sql`), que re-codifica la salida a CP437/oem. Al restaurarlo, MySQL guardó los caracteres corruptos. El dump ya contenía los bytes corruptos (verificado).
- ALCANCE: 357 valores corruptos en 9 tablas (animales, auditoria_logs 324, branding_settings, fundos, landing_blocks, medicamentos, razas, roles, scheduled_session_tasks) + 1 DEFAULT de columna (`branding_settings.tagline DEFAULT 'Gesti├│n rural'`).
- FIX BD: script PHP que revierte la corrupción: mapa CP437 (byte→carácter) con `iconv('CP437','UTF-8',chr($b))` + 2 PARCHES de la variante Windows/.NET que glibc NO tiene: `"®"→0xA9` (é) y `"À"→0xB7` (·). El resto (├│→ó, ├¡→í, ├▒→ñ, ├║→ú, etc.) lo resuelve iconv. Se recorre toda la BD, se detecta `├`/`┬` (U+251C/U+252C) y se convierte carácter a carácter, validando `mb_check_encoding`. También se corrigió el DEFAULT con ALTER TABLE.
- COMANDOS CORRECTOS (prevenir): GENERAR dump con `mysqldump -u root --default-character-set=utf8mb4 --single-transaction --skip-lock-tables --result-file="archivo.sql" fundo_parque01` (NUNCA `> archivo` en PowerShell, re-codifica a CP437). RESTAURAR con `cmd /c "mysql --default-character-set=utf8mb4 -u root fundo_parque01 < archivo.sql"`. El servicio interno de backup (FundoDatabaseBackupService) es seguro (fwrite + PDO quote).
- BACKUPS disponibles en storage/app: `backup_antes_corregir_mojibake_20260808.sql` (estado corrupto), `backup_limpio_post_mojibake_20260808.sql` (327KB, LIMPIO, usar este).

## AUTOFILL LOGIN "un campo negro y otro blanco" (2026-08-08)
- El modal de login (resources/views/livewire/welcome/login-modal.blade.php) tenía AMBOS inputs con la misma clase CSS, pero el navegador (Chrome/Edge) pintaba el campo CONTRASEÑA con su tema al AUTORELLENAR credenciales guardadas → se veía "negro/teal oscuro" vs el otro "blanco".
- FIX: CSS anti-autofill en resources/css/app.css: `input:-webkit-autofill` con `-webkit-text-fill-color` + `-webkit-box-shadow: 0 0 0 1000px <color> inset` + `transition: background-color 9999s`. Modo claro #022c22/#ffffff, modo oscuro #ecfdf5/#06261d. Recompilar con `npm run build` (el build viejo no tenía el CSS).

## DATOS SOFT-DELETED POR CLICS ACCIDENTALES DEL NAVEGADOR (2026-08-08)
- Durante la verificación con clics en refs de accesibilidad OBSOLETOS, se eliminaron 11 sanidad_registros (ids 5, 8, 9-17) a las 15:47-15:49 (soft-delete, deleted_at set). Síntoma: monitoreo mostraba 0 casos aunque la BD tuviera 16 (modelo con SoftDeletes filtra deleted_at).
- FIX: `DB::table('sanidad_registros')->whereBetween('deleted_at', ['2026-08-08 15:47:00','2026-08-08 15:50:00'])->update(['deleted_at'=>null])`.
- LECCIÓN: NUNCA usar refs de accesibilidad de snapshots viejos para clics en páginas Livewire (los refs cambian y el clic cae en otro botón, pudiendo eliminar datos). Usar selectores robustos de Playwright (`button[aria-label="..."]`, `page.locator('table tbody tr').first()`). Los botones "Ver detalle"/"Eliminar" de x-table-action usan aria-label (sin texto visible).

## Convenciones
- Nombres en español (código, tablas plural, columnas, permisos `modulo.accion`)
- Casts `Uppercase`/`Lowercase` para normalizar texto
- Permisos RBAC `modulo.accion` (crear/leer/actualizar/eliminar/exportar), 7 roles protegidos
- Soft deletes en casi todos los modelos operativos

## Seguridad
- Backups ZIP AES-256 + HMAC + SHA-256, retención 10, solo admin
- Auditoría automática con poda 180 días, sesiones activas controladas (idle timeout)
- Rutas requieren auth + verified + fundo activo

## Comandos útiles
- `php artisan fundo:provision` (provisiona fundo + admin)
- `php artisan db:seed --class="Database\Seeders\DemoDataSeeder"` (solo local/testing)
- `composer test` (tests en SQLite memoria)

## Otros
- `refactor.py`: extrae métodos de Ajustes/Index hacia traits
- `DEPLOYMENT.md`: guía de despliegue
- Cache dashboard `dashboard.stats.v2` (3 min), branding `system_branding.settings.v2`

## Alertas propias (SweetAlert2)
- `app.js`: `window.Swal = Swal`, `window.confirmDelete(title, text)` (confirmación), `showToast`, `showModal` (toast/modal). Clases CSS `agro-alert--{success,error,warning,question}` en `app.css`.
- Patrón en vistas: `x-on:click.prevent="confirmDelete('¿...?', '...').then((res) => { if (res.isConfirmed) $wire.metodo() })"`.
- Desde PHP: `$this->dispatch('swal:toast', [...])` o `swal:confirm` / `swal:modal`.
- LECCIÓN: si `window.Swal` es undefined en runtime → el bundle de Vite está desactualizado → ejecutar `npm run build` (el bundle minificado debe contener `window.Swal`).
- z-index: los modales usan `z-[9999]`; SweetAlert2 requiere `.swal2-container { z-index: 100000 !important }` en app.css para mostrarse DELANTE de los modales.
- PRECAUCIÓN: al probar flujos destructivos (borrar auditoría) con clics programáticos, el diálogo de confirmación puede resolverse como "Sí" accidentalmente → borra datos. Probar con cuidado/cancelar explícitamente.
- El navegador simple de VS Code NO renderiza PDFs (limitación); Edge/Browse Lite/Chrome sí.

## Gestión de sesiones (Ajustes → seguridad)
- `user-security.blade.php` (incluido en index cuando `$showUserSecurityModal`): política + tabla "Sesiones por equipo".
- Añadido: paginación (`securitySessionsPerPage`, página `securitySessionsPage`) + footer "Mostrar" con `x-filter-select` + rango + `components.pagination`.
- Añadido: icono borrar (trash) + modal `showUserSecurityDeleteModal` con alcance `revoked`/`all` → `deleteUserSessions($scope)` (valida, borra `user_sessions`, auditoría `sesion.limpieza`, toast). Confirmación con `confirmDelete` + `$wire.deleteUserSessions(mode)`.
- Almacenado en `HasUserManagement.php`.

## Backups SIN auditoría (decisión usuario 2026-08-05)
- Quitado componente `audit` de la creación de backups: checkbox "Incluir Auditoría", config `backup_include_audit`, componente en `generateBackup` y `RunScheduledBackups`, y escritura `auditoria_logs` en `systemTableQueries` (servicio).
- RESTORE mantiene compatibilidad con backups viejos que contienen audit (`restoreSystemWebRows`, `normalizeComponents` con audit, `validateManifest`).
- Test `AjustesModuleTest::test_backup_import_...` assert actualizado: 'Sin límite artificial de velocidad.' → 'Sin límite de velocidad' (falla pre-existente, texto ya removido del blade antes de esta sesión).
- Error "Existen archivos referenciados que no están disponibles: N" = referencia huérfana en BD (foto/comprobante sin archivo en disco). El backup falla por diseño (integridad). Fix: limpiar referencia huérfana (`comprobante_ruta = null` etc.). Diagnóstico: script que cruza columnas de archivos (`animales.foto_ruta` public, `movimientos.comprobante_ruta` local, `asignaciones_familiares.foto_ruta` local, `registro_fotos.ruta` local, `sanidad_registros.evidencia_ruta`, `fundos.logo_ruta`, media) con `Storage::disk()->exists()`.

## Date-picker personalizado
- `resources/views/components/date-picker.blade.php`: selector de fecha Alpine propio (el nativo `<input type="date">` no se puede estilizar — popup cuadrado del navegador).
- Patrón igual a `filter-select`: trigger rounded-xl + menú teleportado a body `z-[100000]`, calendario con navegación de meses, días con `disabled` fuera de mes, selección con `$wire.entangle(model).live`, formato `dd/mm/aaaa`.
- Uso: `<x-date-picker model="deleteFrom" placeholder="dd/mm/aaaa" />`.
- Props: `model`, `placeholder`, `:min`, `:max` (días fuera de rango deshabilitados). Ej: `:max="now()->toDateString()"` para no permitir fechas futuras.
- APLICADO EN TODO EL SISTEMA (2026-08-05): los 36 inputs `type="date"` de 17 archivos reemplazados. 0 inputs nativos restantes. Incluye modelos anidados `dosisProgramadas.{{ $index }}.fecha` (entangle `.live` funciona con dot-notation). 26/26 tests OK, verificado en navegador (animal filtros, leche/nuevo con max, profilaxis/nueva).
- LECCIÓN: en teleport fijo, `offsetParent === null` aunque esté visible (falsos negativos en tests); usar `getComputedStyle(display)` o Playwright `:visible`.
- LECCIÓN CRÍTICA (bug 2026-08-05): **`x-bind:disabled` DENTRO de `x-for` NO funciona** en Alpine+Livewire3+`x-teleport` — deja el atributo `disabled` en TODOS los botones (no se puede seleccionar nada) aunque los datos del scope estén bien. `:class`, `@click` y `x-text` SÍ funcionan. FIX: NO usar `:disabled` en ítems de x-for; usar `:class` para estado visual (atenuado `opacity-30 cursor-default`) + guard lógico en el handler (`pick()` valida y retorna si fuera de rango/fuera de mes).
- `@click.outside` en el div del componente NO distingue el menú teleportado a body (todo clic dentro del menú lo cierra → no se podía navegar meses). FIX: listener manual en `init()` con `destroy()` que ignora clics dentro de `$refs.menu` o `$el`.
- `new Date().toISOString()` es UTC — comparar con fechas locales (`new Date(y,m,d).toISOString()`) falla según hora/zona. FIX: construir el ISO local con `getFullYear/getMonth/getDate`. Hoy se resalta con `bg-indigo-100 ring-indigo-500` (estilo local, no UTC).
- Rediseño v2 (2026-08-05, pedido usuario): el trigger ahora es un `<input type="text">` ESCRIBIBLE + botón icono calendario (toggle). Parse manual en `parseDraft()`: acepta `dd/mm/aaaa` (prioridad, formato peruano), `mm/dd/aaaa` SOLO si dd/mm inválido (b>12 → mm/dd), `aaaa-mm-dd`, separadores `/ - .` y años de 2 dígitos. Commit en Enter (cierra) o blur (no cierra; cierra el click-outside). Inválido o fuera de min/max → revierte silencioso. Hoy: CÍRCULO rosa `rounded-full bg-rose-500 text-white`. Header con navegación de AÑO (dobles chevrons) además de mes. 44/44 tests OK.
- BUG parse corregido: NUNCA asumir mm/dd cuando ambos números ≤12 (`06/08/2026` debe ser 6-ago, no 8-jun). Regla: `a>12 → d=a,mo=b; b>12 → d=b,mo=a; ambos ≤12 → d=a,mo=b` (dd/mm).

## Zona de peligro (borrado total, 2026-08-05)
- Nueva pestaña 'peligro' en Ajustes (admin-only, igual que 'backup'): `canAccessSettingsTab`, `firstAccessibleSettingsTab`, `mount`, `settingsTabAccess` y blade ($settingsTabs + grid-cols-5 + icono mobile + @include).
- `app/Livewire/Ajustes/Traits/HasDangerZoneManagement.php`: `openDangerDeleteModal`/`closeDangerDeleteModal`/`confirmDangerDelete`. Pide contraseña (`Hash::check` contra `auth()->user()->password`); incorrecta → `addError('dangerPassword')`, NO borra. Checkbox `dangerCreateBackup` → crea backup TYPE_COMPLETE ANTES de borrar (si falla, cancela). Borra con `FundoDatabaseBackupService::deleteFundoData($fundoId)` (hice el método PUBLIC, era private) + `deleteAuditLogs` (patrón deleteLogsQuery de Auditoria/Index: fundo_id + globales NULL vinculados por usuario) + borra archivos de disks public/local recolectados ANTES (`collectOperationalFiles`: animales/lotes/ordeno_fotos/producciones_queso→public; movimientos/asignaciones/sanidad_evidencia/registro_fotos→local). `Cache::forget('dashboard.stats.v2')`. Al final registra auditoría `datos.borrados` (queda 1 log). Conserva: usuarios, roles, config_%(backup), backups.
- Vista: `resources/views/livewire/ajustes/danger-zone.blade.php` (tarjeta roja Se elimina/Se conserva + modal con checkbox backup + password).
- Tests: `test_danger_zone_requires_admin_password_and_wipes_operational_data` (contraseña mala→error/no borra; buena→borra). `test_settings_tabs_...` actualizado con 'peligro' => false.
- Verificado en navegador con borrado REAL (datos demo): todas las tablas operativas → 0; usuarios/roles/config/backups conservados; backup creado antes. Para restaurar demo: tab Backups → restaurar el backup recién creado.

## Cambio de tema (dark/light) DE GOLPE (2026-08-05)
- BUG: al alternar tema se veía "por partes" — la clase `dark` se aplicaba al instante PERO `body` tenía `transition-colors duration-250ms` y otros componentes `duration-200/150` → cada elemento fluía a distinta velocidad (fondos pasaban por colores intermedios).
- FIX: `theme-init.blade.php` `applyTheme()` ahora añade la clase `theme-switching` al `<html>` ANTES del toggle y la quita con doble `requestAnimationFrame`. La clase CSS `html.theme-switching * { transition: none !important }` YA existía en app.css pero nunca se activaba desde JS.
- También se quitó el `:class="{ 'dark': darkMode }"` redundante del `<html>` en `layouts/app.blade.php` y `welcome.blade.php` — fuente única de verdad = `$watch('darkMode')` → `window.setTheme()` → `applyTheme`.
- Verificado: bodyBg salta directo al color final en t=0 (antes oklab 0.45→0.81→0.95→final). Sistema y landing OK. localStorage persiste.
- Nota: `welcome.blade.php` usa `aria-label="Cambiar tema"`; el sistema usa `aria-label="Alternar tema"`.

## Toasts de guardado: USAR session()->flash('swal') (2026-08-07) — 192/192 tests
- BUG: al crear/editar Lote, queso, sanidad, etc., NO aparecía el toast "¡Registrado!" encima. Causa: esos formularios usaban `session()->flash('success', ...)` — pero el layout lee `session('swal')` (`#swal-flash` en `layouts/app.blade.php` + `showFlashAlert` en `livewire:navigated`). Clave equivocada → toast nunca se disparaba.
- PRIMER intento fallido: `dispatch('swal:toast')` (como Animal) — el toast SÍ aparece PERO se pierde al navegar: con `redirectRoute(..., navigate:true)` Livewire destruye el DOM al hacer SPA → toast desaparece. LECCIÓN: NO usar dispatch para notificaciones previas a un redirect SPA.
- FIX DEFINITIVO: `session()->flash('swal', ['icon'=>'success','title'=>'¡Registrado!'|'¡Actualizado!','text'=>'...'])` ANTES del `redirectRoute(navigate:true)`. El layout renderiza `#swal-flash` con `@json(session('swal'))` y `showFlashAlert` (en `livewire:navigated` y `DOMContentLoaded`) muestra el toast DESPUÉS de navegar → persiste. VERIFICADO en navegador: "¡Registrado! Lote creado correctamente." aparece encima en /engorde tras crear lote.
- Aplicado a: Animal/Form, Engorde/LoteForm, Leche/Form, Finanzas/{Asignacion,Movimiento}Form, Monitoreo/{Parto,Profilaxis,Sanidad}Form, Queso/Form. (Leche/finanzas ya usaban `session()->flash('swal')` — solo se unificó título a ¡Registrado!/¡Actualizado!.)
- `dispatch('swal:toast')` se mantiene SOLO en acciones que NO navegan (delete, landing manager, etc.) — ahí sí es correcto.

## Lazy load de relaciones en props Livewire → 500 (2026-08-07) — 192/192 tests
- BUG: al guardar en `/leche/nuevo` (y al re-renderizar tras validación) daba **500 "Attempted to lazy load [raza]"**. Causa: `Leche/Form::$vacas` guardaba **modelos Eloquent** con relaciones (`raza`, `especie`) eager-loaded. Livewire **deserializa** las props públicas entre requests y **pierde las relaciones** → la vista `data_get($vaca, 'raza.nombre')` disparaba lazy load → `Model::preventLazyLoading` lo bloquea → 500. Ocurre con CUALQUIER fecha, no solo futura (el usuario lo notó al cambiar fecha).
- FIX (consistente con Profilaxis/Sanidad): `queryAllowedVacas()` ahora hace `->filter()` sobre modelos (para `canBeMarkedForMilking`) y LUEGO `->map()` a **arrays planos** (`id`, `arete`, `nombre`, `raza` = string nombre, `especie`, `genero`). La vista usa `data_get($vaca, 'raza', '-')` en vez de `'raza.nombre'`.
- REGLA: en Livewire, NUNCA guardar modelos Eloquent con relaciones en props públicas si la vista accede a las relaciones — convertir a arrays planos en el método que las carga (patrón Profilaxis/Sanidad/Leche). `loadVacas()`/`loadAnimales()` en render/mount.
- Verificado: guardado exitoso con fecha pasada → toast "¡Registrado!" + redirect a /leche. Sin 500. 192/192.

## Formulario Leche: modo lote autocompleta vacas + rediseño (2026-08-07) — 193/193 tests
- **Modo lote autocompleta "Vacas ordeñadas"**: `updatedTipoRegistro()` y `updatedFecha()` (si `tipoRegistro === 'lote'`) calculan `queryAllowedVacas()->count()` y lo ponen en `$this->cantidadVacas`. SOLO si `count > 0` (si no, conserva valor actual — evita romper validación min:1 en tests/edición sin vacas).
- Rediseño `form.blade.php` de leche: headers de sección con icono en caja `bg-emerald-500/10` + badge de turno en "Datos del turno"; labels con iconos (calendario, sol, documento); "Resumen de producción en lote" con icono de barras, input de vacas con sufijo "vacas" y nota "Se completa automáticamente con las vacas aptas para esta fecha." Mantiene paleta emerald/zinc del sistema.
- Nuevo test `test_batch_mode_auto_fills_milked_cows_from_eligible_animals` (crea 3 bovinas aptas → tipoRegistro lote → cantidadVacas '3' → guarda y verifica 3 en BD).
- PATRÓN diseño: secciones con icono en caja coloreada + labels con iconitos = "más vida" sin exceso de color (consistente con sistema emerald/zinc).

## Uniformidad de formularios: max-w-6xl (2026-08-07) — 193/193 tests
- BUG de proporción: `queso/form.blade.php` usaba `max-w-4xl` (~896px) mientras leche/animal usan `max-w-6xl` (1152px) → el formulario de queso se veía más angosto y desproporcionado.
- FIX: `max-w-4xl` → `max-w-6xl` (mx-auto), y la foto `aspect-[4/3] lg:aspect-square` (como leche) en vez de `aspect-square` siempre. Inputs de presentación `py-2.5` → `py-3` y `h-[42px]` → `h-[46px]` (subtotal + botón eliminar) para igualar altura con el resto.
- Verificado: contenedor 1152px igual que leche; columnas datos ~710px + foto 368px (misma proporción). 193/193 tests.
- LECCIÓN: todos los forms de ingreso deben usar `mx-auto max-w-6xl space-y-6` + grid `lg:grid-cols-3` (datos col-span-2 + foto 1 col) para proporción uniforme.
- AMPLIADO a TODOS los forms de ingreso (2026-08-07): finanzas/{movimiento,asignacion}-form y monitoreo/{parto,profilaxis,sanidad}-form pasaron de `max-w-5xl` → `max-w-6xl` (1152px). Verificado en navegador: los 8 forms miden 1152px. Los `show` (detalle) conservan su ancho menor a propósito (lectura). 193/193 tests.

## Rediseño formulario finanzas/movimiento (2026-08-07) — 193/193 tests
- El form de movimiento era plano (blanco/zinc en claro, zinc-900 en oscuro). Rediseñado con "vida" compatible con el sistema:
  - Header: icono billete en caja `bg-emerald-500/10` + badge dinámico tipo (Egreso=rose / Ingreso=emerald, cambia con `$tipo`).
  - Form container: `bg-gradient-to-br from-white via-emerald-50/40 to-white` (claro) / `from-zinc-900 via-emerald-950/20 to-zinc-900` (oscuro) + borde `border-emerald-950/10`.
  - Sección "Datos del movimiento": icono en caja emerald (barras).
  - Aside "Comprobante y Foto": `bg-gradient-to-b from-emerald-50/60 to-white` (claro) / `from-emerald-950/25 to-zinc-900` (oscuro) + icono teal (cámara) + drop `bg-white/70`.
  - Footer: `bg-gradient-to-r from-emerald-500/5 via-transparent to-teal-500/5` + botón Cancelar con borde (ya no transparente).
- PATRÓN: gradientes emerald/teal muy sutiles (`/5`, `/10`, `/20`, `via-.../40`) dan vida en claro sin romper oscuro. Badges dinámicos según estado.

## Borrado de alertas programadas solo-admin (2026-08-07) — 196/196 tests
- Usuario pidió opción de BORRAR alertas en `/monitoreo?tab=alertas`, SOLO admin, con modal con icono de borrado.
- Backend `Monitoreo/Index.php`: props `showDeleteAlertaModal` (bool), `deleteAlertaId` (?int), `deleteAlertaData` (array con fecha/animal/tipo/mensaje/leida para el modal). Métodos `openDeleteAlertaModal(int $id)` (verifica admin con `authorizeFundoAdmin()`, carga datos de la alerta con `with('animal')`), `closeDeleteAlertaModal()`, `deleteAlerta()` (verifica admin, busca `AlertaProgramada::where('fundo_id', session('fundo_id'))->find(...)` para scoping multi-fundo, borra con `->delete()`, cierra modal, `resetPage('alertaPage')`, registra auditoría `app(AuditLogger::class)->record('alerta.eliminada', 'monitoreo', ..., metadata: [...])`, toast `swal:toast` "¡Eliminada!").
- SEGURIDAD: `currentUserIsFundoAdmin()` (usa `fundos->firstWhere('id', session('fundo_id'))?->pivot?->es_administrador`) y `authorizeFundoAdmin()` → `abort_unless(..., 403, 'Solo administradores del fundo pueden realizar esta acción.')`. Mismo patrón que Ajustes/Index.
- VISTA: botón `x-table-action type="delete"` en la columna Acciones SOLO si `$puedeBorrarAlertas` (pasado desde render: `compact(...) + ['puedeBorrarAlertas' => $this->currentUserIsFundoAdmin()]` — ¡OJO! el render usa `compact()` explícito, así que computed properties NO se pasan solas; hay que añadirlas al array).
- MODAL (diseño estilo Auditoria/Index): overlay `fixed inset-0 z-[9999] bg-zinc-900/60 backdrop-blur-md dark:bg-black/70` con `x-show="$wire.showDeleteAlertaModal"`, `x-on:keydown.escape.window="$wire.closeDeleteAlertaModal()"`, `x-on:click.self="$wire.closeDeleteAlertaModal()"`, `x-init` para bloquear scroll de body. Tarjeta `max-w-lg rounded-3xl`. Header: icono papelera en caja `bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400` + kicker "Monitoreo · Alertas" + título. Cuerpo: `<dl>` con los datos de `$deleteAlertaData` (Fecha, Animal, Tipo con `x-status-badge`, Mensaje, Estado) + aviso amber "Acción permanente e irreversible: Se eliminará solo esta alerta. El animal y sus dosis de profilaxis permanecen intactos." Footer: Cancelar (borde zinc, `wire:click="closeDeleteAlertaModal"`) + Eliminar (`bg-rose-600`, `wire:click="deleteAlerta"`, con spinner).
- IMPORTANTE (integridad): borrar una alerta NO toca ni al animal (FK `nullOnDelete`) ni a la dosis de profilaxis (FK `cascadeOnDelete` va en dirección dosis→alerta, no al revés). La alerta es una fila independiente.
- Tests: `tests/Feature/MonitoreoAlertaDeleteTest.php` — 3 tests: (1) admin puede abrir modal y borrar (verifica `deleteAlertaData.*`, `assertDatabaseMissing` alerta, `assertDatabaseHas` animal intacto, auditoría `accion=alerta.eliminada`); (2) no-admin `puedeBorrarAlertas=false` + `call('openDeleteAlertaModal')->assertForbidden()` (¡en Livewire tests `abort_unless` se captura como 403, no como excepción!); (3) borrar alerta de OTRO fundo no encuentra nada (scoped por fundo).
- Columna auditoría: se llama `accion` (NO `evento`) en `auditoria_logs` (migración 2026_07_13). `AuditLogger::record(event, module, detail, targetUser=null, metadata=[], fundoId=null, result='exitoso', actor=null)`.

## BORRADO MASIVO de alertas programadas (2026-08-07) — 203/203 tests
- Usuario: "cuando haya muchos registros es muy tedioso eliminar uno por uno". Añadido borrado masivo en `/monitoreo?tab=alertas` (solo admin).
- Backend `Monitoreo/Index.php`: prop `selectedAlertas` (array de IDs). Métodos: `toggleAlertaSeleccion(int $id)` (toggle con `array_search`, reindexa con `array_values` — el orden cambia al re-agregar, va al final), `toggleSelectAllAlertas()` (selecciona/des-selecciona TODAS las que cumplen filtros vía `filteredAlertaQuery()`), `clearAlertasSeleccion()`, `openDeleteAlertasMasivoModal(string $mode)` (mode `'seleccion'` | `'filtradas'`; si count=0 → `swal:toast` "Nada que eliminar" sin abrir modal), `closeDeleteAlertasMasivoModal()`, `deleteAlertasMasivo()` (transacción `$query->delete()`, limpia selección, `resetPage('alertaPage')`, auditoría `'alertas.eliminadas_masivo'` con metadata cantidad/modo/tipos, toast "¡Eliminadas!").
- Helper privado `filteredAlertaQuery()`: `AlertaProgramada::where('fundo_id', session('fundo_id'))` + `applyAlertaFilters($query)` — usado por selección total y modo filtradas.
- VISTA tabla alertas: (1) columna checkbox (solo `$puedeBorrarAlertas`) — header con `wire:click="toggleSelectAllAlertas"` + `@checked(count($selectedAlertas) > 0 && count($selectedAlertas) === $alertas->count())` (title dinámico "Seleccionar/Quitar selección de esta página"), filas con `wire:click="toggleAlertaSeleccion(id)"` + clase fila `bg-rose-500/[.06]` si seleccionada; (2) colspan vacío pasa de 6 a 7; (3) BARRA de acciones masivas sobre la tabla (solo admin): badge "N seleccionadas" (rose pill) + "Limpiar selección" (borde zinc) + "Eliminar seleccionadas" (`bg-rose-600`) SOLO si hay selección; + botón "Eliminar todas las filtradas (N)" (`border-rose-500/25`) SOLO si `$alertaAnyFilter && $alertas->total() > 0` (usa `$alertas->total()` = total filtrado).
- MODAL masivo: igual estilo al individual (overlay `z-[9999]`, tarjeta `max-w-lg`), header icono documento rose + título dinámico "Eliminar alertas seleccionadas/filtradas", tarjeta conteo grande `bg-rose-600` con número `text-2xl font-black` + "alerta(s) serán eliminadas", aviso amber permanente, footer Cancelar (borde) + "Eliminar N" (`bg-rose-600`, con spinner).
- LECCIÓN render: las computed properties NO llegan solas al render porque usa `compact()` explícito — pasar con `+ ['puedeBorrarAlertas' => ...]`. `selectedAlertas` es prop pública normal (sí llega sola).
- Tests `MonitoreoAlertaDeleteTest` (7 total): +4 nuevos — `test_administrator_can_select_and_bulk_delete_alertas` (toggle 3, deseleccionar 1, re-seleccionar va al final `[1,3,2]`, borrar 3, verifica no-seleccionada y animal intactos, auditoría masiva), `test_administrator_can_bulk_delete_filtered_alertas` (filtro `alertaFiltroLeida='1'` → borra solo las 3 leídas, conserva pendiente), `test_bulk_delete_with_no_selection_shows_warning_and_keeps_data` (sin selección: modal NO abre + `assertDispatched('swal:toast')` + datos intactos), `test_non_administrator_cannot_bulk_delete` (toggle no-op + `assertForbidden`).
- Nota: al verificar en navegador se borraron TODAS las alertas demo (6 quedaban). El usuario pidió re-seedear si hace falta.

## REDISEÑO SALUD ANIMAL — datos conectados (2026-08-07/08) — 208/208 tests
- Usuario aprobó plan de rediseño del módulo Monitoreo → "Salud y Reproducción Animal". Objetivo: los datos se comuniquen por animal (historial unificado), con plan de tratamiento de dosis múltiples y seguimiento de recuperación.
- NUEVA TABLA `tratamiento_dosis` (migración `2026_08_07_020000`): fundo_id, sanidad_registro_id (FK cascade), numero, medicamento_id (FK null), medicamento_nombre, dosis, via, fecha_programada, fecha_aplicada, aplicada (bool), responsable. + Columnas en `sanidad_registros`: `fecha_cierre`, `observaciones_cierre`. + Columnas en `profilaxis_dosis_programadas`: `fecha_aplicada`, `aplicada`.
- MODELOS: `TratamientoDosis` (casts Lowercase para nombre/dosis/via/responsable, igual que SanidadRegistro; `casoClinico()`, `medicamento()`). `SanidadRegistro` ahora tiene `dosisPlan()` (hasMany orderBy numero), `dosisAplicadas()`, `dosisPendientes()`, y fillable+casts para fecha_cierre/observaciones_cierre (Lowercase). `ProfilaxisDosisProgramada` gana aplicada/fecha_aplicada.
- `SanidadForm` REDISEÑADO: props `dosisPlan` (array) + `medicamentos` (catálogo del fundo/global). Se ELIMINARON `medicamentoNombre` y `dosisVia` (reemplazados por el plan). En mount nuevo: inicializa "Aplicación 1 · Aplicada" con fecha hoy. `addDosis()` agrega fila con fecha sugerida = día siguiente de la última. `removeDosis()`. `updatedDosisPlan()` limpia nombre libre si hay catálogo. Save: valida `dosisPlan` (opcional, sin min:1 — puede no haber medicamento), normaliza (si medicamento_id seleccionado ignora nombre libre; filas sin medicamento se descartan salvo que sea única), marca recuperada → fecha_cierre=hoy, en transacción borra dosis previas y recrea con `numero` secuencial. Estado clínico AHORA SOLO: en_tratamiento, recuperada, critico, cuarentena (SE ELIMINÓ 'baja' — redundante con Animal→Gestionar). Textos: "Registrar/Editar Caso Clínico", "Datos del Caso", "Estado del caso".
- `Monitoreo/Index` SEGUIMIENTO: `marcarDosisAplicada(int $dosisId)` (marca aplicada+hoy, toast con pendientes restantes), `openRecuperarCasoModal(id)` + `confirmarRecuperacion()` (valida fecha<=hoy + observaciones, pone estado=recuperada + fecha_cierre + cierra alertas cuarentena del animal), `marcarDosisProfilaxisAplicada(int $dosisId)` (marca dosis de profilaxis y cierra alertas de próxima dosis). Props de modal: showRecuperarCasoModal, recuperarCasoId, recuperarCasoData, recuperarCasoFecha, recuperarCasoObservaciones. Render de sanidad con `with(['animal','medicamento','dosisPlan.medicamento'])` (¡OJO: lazy load de medicamento en dosisPlan si no se eager-load!).
- VISTA tabla sanidad: chips de dosis (verde=✔ aplicada / ámbar=⏳ pendiente con botón "aplicar" inline), badge "✔ Alta {fecha}" si cerrado, botón `x-table-action type="recover"` (nuevo tipo en table-action: emerald check) para casos en tratamiento/critico/cuarentena. Tabla profilaxis muestra calendario igual (fechasDosisProgramadas).
- `Animal/Show` LÍNEA DE TIEMPO: `buildTimeline()` une sanidadRegistros (con dosisPlan), profilaxis del animal y partosMadre → orden cronológico desc. `currentClinicalStatus()`: inactivo si no activo; sano si último caso recuperada; alerta si critico/cuarentena; tratamiento si en_tratamiento. En vista: badge "🟢 Sano / 🟠 En tratamiento / 🔴 Crítico" + pestaña "🧬 Salud" (primera, default) con timeline (icono redondo + tarjeta con título enlazado, estado, dosis ✔/⏳, fotos). Se mantienen pestañas Historial/Partos/Lácteo.
- RENOMBRADO terminología veterinaria: tabs "Casos Clínicos", "Prevención", "Reproducción", "Alertas"; botones "Registrar Caso Clínico", "Nueva Prevención"; filtros "casos clínicos", "prevención"; KPIs "Casos en Tratamiento", "Casos Clínicos"; PDFs "Casos clínicos"/"Prevención"; título página "Salud y Reproducción Animal". Actualizados tests que assertearon textos viejos.
- `PartoForm`: al crear parto con cría viva → madre `estado_reproductivo='lactante'`; aborto/cría muerta → `'seca'`.
- LECCIÓN precedencia PHP: `->map(...)->join(' | ')` SE ROMPE si se escribe `.join(' | ')` (el `.` concatena con la función global join → TypeError). SIEMPRE `(...)->join(...)` con paréntesis.
- Tests NUEVOS `MonitoreoTratamientoTest` (5): guarda plan con catálogo+libre (3 dosis, lowercasts), edición reemplaza plan y conserva estado, marcar dosis + recuperar caso (cierra alerta cuarentena), ya no ofrece 'baja', timeline unificada en Show (badge tratamiento).

## Plantilla UNIFICADA de formularios (2026-08-07) — 193/193 tests
- El usuario pidió usar el rediseño de `finanzas/movimiento-form` como PLANTILLA para TODOS los formularios ("usa esa misma plantilla, unifica el diseño").
- CONVERTIDOS (todos los forms de ingreso, 9 total): movimiento (referencia), parto, profilaxis, sanidad, asignación, animal, lote-engorde, leche, queso. Verificado en navegador (dark + light) y 193/193 tests (cambios solo blade).
- PATRÓN de la plantilla (repetir en cada form):
  1. **Header**: `flex items-start gap-4` → back link + `<div class="min-w-0 flex-1">` con fila `flex flex-wrap items-center gap-2` = [icono en caja `h-9 w-9 rounded-xl bg-{color}-500/10 text-{color}-600 dark:bg-{color}-400/10 dark:text-{color}-400`] + kicker `text-xs font-bold uppercase tracking-[0.18em] text-{color}-600 dark:text-{color}-400` + badge dinámico `hidden sm:inline-flex ... rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-widest` (Nuevo registro=color del módulo / Editando=sky con dot `bg-sky-500`). Luego `h1.mt-1 text-2xl sm:text-3xl` + descripción.
  2. **Secciones**: `rounded-2xl border border-emerald-950/10 bg-gradient-to-br from-white via-{color}-50/40 to-white p-5 sm:p-6 shadow-sm dark:border-zinc-800 dark:bg-gradient-to-br dark:from-zinc-900 dark:via-{color}-950/15 dark:to-zinc-900` + header `flex items-center gap-3 border-b border-emerald-950/10 pb-3 dark:border-zinc-800` con icono en caja + título `text-sm font-bold uppercase tracking-wider text-{color}-700 dark:text-{color}-400` + descripción.
  3. **Aside foto**: `bg-gradient-to-b from-{color}-50/60 to-white` / dark `from-{color}-950/25 to-zinc-900`, icono cámara teal.
  4. **Footer acciones**: `flex ... rounded-2xl border border-emerald-950/10 bg-gradient-to-r from-emerald-500/5 via-transparent to-teal-500/5 px-5 py-4 dark:border-zinc-800` + Cancelar bordeado `rounded-xl border border-zinc-300 bg-white text-zinc-600 ... dark:border-zinc-700 dark:bg-transparent dark:text-zinc-400`.
- COLORES por módulo: movimiento/leche/lote/animal=emerald, parto=rose (madre)/teal (cría), profilaxis/sanidad=emerald/rose (sanidad: identificación=rose, medicación=amber), asignación=violet/indigo, queso=amber (producción)/teal (foto), observaciones=amber en todos.
- Badge dinámico: `$isEdit ? sky 'Editando' : color 'Nuevo registro'` — así en todos (movimiento usa estado del `$tipo` en su lugar).
- LECCIÓN: gradientes `/5 /10 /15 /40` sutiles dan "vida" sin romper dark mode; iconos SVG Heroicons outline `stroke-width="1.8"` tamaño `h-4.5 w-4.5` en cajas de 9×9.

## Selects nativos → x-filter-select (2026-08-07)
- Migrados los ÚLTIMOS `<select>` nativos al componente `filter-select` (dropdown Alpine teleportado, teclado, checkmark). Ya NO queda ningún `<select>` nativo en resources/views (grep `<select` = vacío).
- Migrados: `animal/index.blade.php` "Motivo de baja" (tone=rose, live), `queso/form.blade.php` "Peso unitario" (tone=emerald, live, dentro de @foreach con dot-notation `presentaciones.{{ $index }}.peso_gramos` — entangle .live funciona), `ajustes/user-security.blade.php` "Unidad" (tone=sky, compact, en sub-modal de tareas programadas).
- Verificado en navegador: selección y binding OK (queso subtotal 0.50 kg al elegir 500 g; unidad "Horas" se guarda).
- LECCIÓN: para migrar un select, `x-filter-select` acepta `model` con dot-notation (arrays anidados), `:options` array clave=>label, `tone` (emerald/sky/rose/amber/indigo/cyan/violet), `live` (entangle .live), `compact`.

## Auditoría de errores post-composer-update (2026-08-07)
- El proyecto se actualizó a Laravel 13.19 + Livewire 3.8.2 (antes 13.8). Tras la actualización, 10 componentes usaban `redirect()->route('x')->navigate()` que **NO existe** en Livewire 3.8.2 → `Method Redirector::navigate does not exist` → **500 en CADA guardado** (animal, leche, queso, finanzas, monitoreo, engorde) → la página recarga y parece "borrar todo y no guardar". FIX: usar `$this->redirectRoute('x', params, navigate: true)` (método correcto en HandlesRedirects). Archivos: Animal/Form, Animal/Index, Engorde/LoteForm, Finanzas/AsignacionForm, Finanzas/MovimientoForm, Leche/Form, Monitoreo/{Parto,Profilaxis,Sanidad}Form, Queso/Form.
- `Component::redirect($url, $navigate=false)` / `redirectRoute($name, $params, $absolute, $navigate)` — así se hace SPA redirect en Livewire 3.8. El `Redirector` (Laravel y el de Livewire) NO tiene `navigate()`.
- ROBUSTEZ: todos los `$store.imageUploads.busy` en forms (11 archivos) ahora usan optional chaining `$store?.imageUploads?.busy` — si el store no existe, un TypeError en `x-on:submit` impediría el preventDefault → submit nativo → recarga y pérdida de datos.
- Tests actualizados: ImageFrameTest `between:1,2.5`→`between:0.3,2.5` (MIN_ZOOM=0.3); ExampleTest `assertSee('Acceso al sistema')`→`assertSee('Acceso privado')` (el welcome cambió el título del modal de login).
- Verificado: navegación SPA OK (navType navigate), guardado de movimiento redirige a /finanzas?tab=movimientos sin recarga, tema sin flash al cargar (light/dark estables desde t=0), suite completa 189/189.
- LECCIÓN: tras `composer update`, verificar métodos de API de Livewire/Laravel usados en el proyecto (grep `->navigate()`, métodos deprecados). El log de Laravel puede NO capturar 500s de requests Livewire en dev si el error se maneja como respuesta.

## Editor de imagen modo 'simple' (2026-08-07)
- `x-image-frame-editor` (components/image-frame-editor.blade.php) ahora acepta `mode="full"` (default) | `"simple"`.
- Modo SIMPLE (usado SOLO en /ajustes/web → landing-manager): frame AMPLIO `--horizontal` (16/10) con borde emerald (sin el cuadrado 1:1), SIN círculo celeste, SIN rejilla/esquinas cropper, SIN "Vista previa final", SIN botones Centrar/Restablecer, título "Ajustar imagen", toolbar "Encuadre de la imagen / Formato amplio". Mantiene: drag, sliders Horizontal/Vertical, Zoom con botones −/+, Cancelar/Guardar.
- Modo FULL (logo en /ajustes?tab=general y resto de módulos) INTACTO.
- Aplicado en landing-manager: el editor de `$showFrameEditor` (línea ~210) y el de imágenes pendientes (línea ~153) con `mode="simple"`.
- Verificado en navegador: /ajustes/web simple OK; logo full OK. El usuario aprobará antes de extender a otros módulos.
- Mejoras modo simple (v2, 2026-08-07): botón "Restablecer" (solo simple), toggle de vista previa "Escritorio / Tablet" | "Móvil" (cambia frame `--horizontal` 16:10 ↔ `--mobile` 4:5; usa CSS `__modes`/`__mode`/`__mode--active` ya existentes), zoom min 1 (la imagen SIEMPRE llena, sin huecos; se normaliza al abrir si valor < minZoom) y max 4 (antes 2.5).
- app.js `imageFrameEditor` acepta `minZoom`/`maxZoom`/`simple`/`screen` en config y usa `this.minZoom/maxZoom` en clamp/sensitivity/reset. `ImageFrame::MAX_ZOOM` 2.5 → 4.0 (backend, para que guarde hasta 4). Test ImageFrameTest actualizado a `between:0.3,4` y normalize zoom 8 → 4.0. Full (logo) sigue min 0.3 / max 4 por default (antes 2.5 — más rango, inofensivo). 189/189 tests.
- Modo simple aplicado GLOBALMENTE (v3, 2026-08-07): el default de `mode` en `x-image-frame-editor` ahora es `'simple'`; el LOGO en `ajustes/index.blade.php` pasa `mode="full"` explícito (el único full). Todos los módulos con foto (animal, engorde, finanzas mov/asign, leche, monitoreo parto, queso, record-photo-upload, landing-manager) quedan simple automáticamente.
- UI modo simple: inspector sin sliders Horizontal/Vertical (solo título "Encuadre" + "Arrastra la imagen para moverla" + botón Restablecer), SOLO zoom (slider + botones −/+, min 0.8 / max 4). El movimiento se hace arrastrando con la manito (drag). Toggle "Escritorio / Tablet" | "Móvil" con **posiciones INDEPENDIENTES** (`switchScreen()` guarda frame en `this.frames[screen]` y restaura; NO persiste por separado — solo durante la edición; al guardar se guarda la pantalla visible).
- Frame simple más amplio: `--horizontal` 16:9 (width 92vh, max-height 58vh); `--mobile` 4:5 un poco más grande (24rem/46vh). Zoom min simple 0.8 (alejar un poco para ver completa).
- Verificado: /ajustes/web y /animal/nuevo simple OK; logo full intacto (3 sliders, círculo, vista previa, frame cuadrado); independencia móvil 1.4× vs escritorio 1.2×; 189/189 tests.
- Fix "negro en el borde" (v4, 2026-08-07): el usuario odiaba el fondo negro del frame cuando el zoom < 1 dejaba huecos. SOLUCIÓN en modo simple: (1) imagen de fondo = la MISMA imagen difuminada (opacity 0.30 + blur-md + scale-110, `aria-hidden`, pointer-events-none) que cubre siempre el frame → nunca se ve negro al alejar; (2) clase CSS `.image-frame-editor__frame--simple` con fondo gris claro `#dfe4ea` (light) / gris oscuro `#141c26` (dark) en lugar del negro `#07110c`. Verificado: frameBgColor rgb(223,228,234) light / rgb(20,28,38) dark; drag funciona (manito, 19%→24.9%); zoom aplicado (scale). 189/189 tests.
- LECCIÓN: el usuario prefiere "la imagen siempre llena" — alejar (<1) genera huecos; si se permite alejar, el fondo debe mostrar la imagen (no un color sólido negro).
- Zoom final modo simple (v5, 2026-08-07): ZOOM MÍNIMO = 1 (limitado a las dimensiones de la imagen; el botón − se deshabilita en 1; solo se amplía hasta 4). Barra de zoom REUBICADA en la PARTE INFERIOR del marco (`.image-frame-editor__zoom-bar` debajo del frame: botón −, slider, botón +, Restablecer). Inspector simple = solo título "Encuadre" + subtítulo + ayuda "Zoom: barra inferior, rueda del mouse o Ctrl + / Ctrl −". Workspace en simple es 1 columna (`.image-frame-editor__workspace--simple`). NUEVOS atajos: `onWheel` (rueda del mouse sobre el frame → zoom, `@wheel.prevent`) y `onKeydown` (Ctrl/Cmd + `=`/`+`/`-`/`0` en el diálogo). La manito (drag) confirmada funcionando (17.5%→7.3%). 189/189 tests.

## Sesión "sin límite" para admin (2026-08-07) — 192/192 tests
- OBJETIVO usuario: opción en el modal Seguridad de cuenta para que la sesión NO tenga límite (que no se bloquee), o programar un tiempo de restauración. SOLO modo admin.
- Semántica NUEVA `session_idle_timeout_minutes` (users): `NULL` = sin límite (solo admin la usa); admin con valor = tiempo programado (5 min – 1 año, respetado sin tope de config); usuario estándar con null = usa `config('session.lifetime')` (30), tope = config lifetime (igual que antes).
- Migración `2026_08_07_000000_add_session_unlimited_support.php`: columna `session_idle_timeout_minutes` → nullable default null; backfill: filas con 30 → null; admins (pivot `fundo_user.es_administrador` OR rol `user_roles`+`roles.nombre='administrador'`) → null.
- `UserSessionService::idleTimeoutFor()` reescrito: admin+null → 525600 (nunca cierra); admin+valor → min(525600, max(5, valor)); estándar → min(config lifetime, max(5, valor?:config)).
- `HasUserManagement`: nueva prop `securitySessionUnlimited`; `openUserSecurityModal` lo setea = canUseUnlimitedSessions && valor===null; `saveUserSessionLimit` guarda `null` si unlimited (valida sin `securityIdleTimeoutMinutes`), si no valida min 5 / max 525600 (admin) o max config lifetime (estándar). Audit añade `inactividad_sin_limite`.
- Modal `user-security.blade.php`: toggle "Sin límite de sesión:" (switch Activo/Inactivo) SOLO si `$securityUserCanUseUnlimitedSessions`; oculta/deshabilita el campo Inactividad cuando está activo; texto ayuda actualizado.
- BUG CRÍTICO encontrado y corregido: `app.blade.php` ponía `data-timeout = 525600*60000 = 31,536,000,000 ms` para admins → `setTimeout` en JS desborda 2^31-1 (~24.8 días) y se dispara en ~1ms → ¡logout instantáneo! Fix: layout emite `data-timeout="0"` cuando es sin límite (JS lo ignora) y `initializeIdleLogout` en app.js añade guard `timeout > 2147483647 → return` (también timeout 0). Verificado: data-timeout="0" en admin, 0 errores JS.
- Tests: actualizado `test_fundo_administrator_can_use_unlimited_sessions` (admin null → 525600); NUEVOS: `test_fundo_administrator_can_program_idle_timeout_without_unlimited` (120→120), `test_non_administrator_cannot_activate_unlimited_session` (no-admin NO puede), `test_idle_timeout_respects_unlimited_and_programmed_values`. Total 192/192.
- NOTA verificación navegador: PEPE también es admin del fundo (badge "Administrador"), por eso ve el toggle — correcto. Ojo al hacer clic en modales con Livewire: un clic con ref obsoleto puede pegar en "Guardar política" y guardar sin querer (me pasó: guardó 30 min al admin; lo restauré a null vía BD).

## Rediseño modal + tareas programadas (2026-08-07) — 195/195 tests
- **Sección "Política de sesión" HORIZONTAL**: `Sesiones` (input con sufijo "disp.") · `Sin límite de sesión:` (switch Activo/Inactivo) · `Inactividad` (con sufijo min) · `Guardar política`, en una fila `xl:grid-cols-4`. El usuario odiaba el diseño vertical.
- **Toggle instantáneo**: el switch de "Sin límite" ya NO usa `wire:click="$toggle"` (hacía roundtrip Livewire lento ~1-2s y re-renderizaba todo el modal). Ahora es ALPINE puro: `x-data="{ unlimited: @js($securitySessionUnlimited), async savePolicy(){ await $wire.set('securitySessionUnlimited', this.unlimited); $wire.saveUserSessionLimit(); } }"`, `@click="unlimited = !unlimited"`, `x-show="!unlimited"` para Inactividad, `:disabled="unlimited"`. Verificado 328ms (instantáneo). El sync al servidor ocurre SOLO al guardar (await $wire.set antes de saveUserSessionLimit).
- **Botón "Programar"** en "Sesiones por equipo" (junto a "Restablecer sesiones" y el icono de eliminar): abre sub-modal "Programar tarea de sesiones" con 2 tipos: **Restablecer sesiones** (revoca todas) / **Limpiar historial** (borra revocadas/expiradas), + "Ejecutar dentro de [N] [minutos|horas|dias]". Muestra "Tareas pendientes" con botón Cancelar.
- **Nueva tabla `scheduled_session_tasks`** (migración 2026_08_07_010000): fundo_id, user_id, tipo (reset|purge), execute_at, status (pending|done|cancelled|failed), result, created_by; índices [status,execute_at] y [user_id,status].
- **Modelo `ScheduledSessionTask`** (trait BelongsToFundo) + **Servicio `ScheduledSessionTaskService`**: `create()`, `cancel()`, `processDueTasks()` (procesa vencidas SIN global scopes, revoca vía `UserSessionService::revokeAll($user,null,'scheduled_reset')` o borra revocadas; en transacción).
- **Ejecución automática (sin cron)**: comando `sessions:process-scheduled` (registrado `Schedule::everyMinute()->withoutOverlapping()` en routes/console.php) + **Middleware `ProcessScheduledSessionTasks`** (registrado en web append) que ejecuta tareas vencidas en cada petición web como respaldo (consulta `exists()` indexada; SKIP en entorno testing).
- **Tabla de equipo**: cuando el usuario es admin Y `session_idle_timeout_minutes === null` → columna Sesiones muestra badge verde "Sin límite" (icono ∞) y columna Inactividad badge "Sin límite"; si no, "X / Y activas" y "N min" (con `?: config lifetime`). Móvil: "sesión sin límite de tiempo" o "X/Y sesiones · cierre N min".
- **TRAMPA BLADE (importante, causa de 19 tests rotos)**: `@else`/`@endif` pegados a texto previo (`tiempo@else`, `min@endif`) NO se compilan porque la regex de Blade usa `\B@` (requiere que `@` NO esté tras una palabra). SOLUCIÓN: calcular el texto en `@php` y usar `{{ }}`. Siempre poner espacio o tag `</span>` antes de `@else/@endif`, o mejor: computar en PHP.
- Tests nuevos: schedule reset + proceso vencido, purge automático, cancelar tarea. Total 195/195.

---

## 2026-08-09 — MONITOREO: MODELO ÚNICO DE SALUD ANIMAL

- `/monitoreo` ya no ofrece modos "clínico" y "preventivo". Hay un solo botón **Registrar evento** y un solo formulario condicional.
- Categorías visibles: lesión, enfermedad, parásitos, vacunación, suplementación, procedimiento, control y otro.
- Migración `2026_08_09_000000_restructure_animal_health_events.php`: añade a `sanidad_registros` `categoria_salud`, `subtipo`, `severidad`, `ubicacion_corporal`, `estado_seguimiento`; conserva columnas antiguas solo como capa interna compatible y migra datos existentes.
- Estados visibles: `en_seguimiento`, `completado`, `critico`, `cuarentena`.
- Un evento pertenece siempre al `animal_id`. Selección múltiple crea un evento por animal, manteniendo trazabilidad individual.
- Dosis D1–Dn viven en `tratamiento_dosis`. Cada dosis se marca aplicada desde la tabla; NO se crea otro formulario para D2/D3.
- `alertas_programadas.tratamiento_dosis_id` enlaza cada alerta nueva con su dosis (FK cascade + unique). Marcar dosis aplicada marca su alerta como leída.
- `Animal/Show` muestra un único historial de salud ordenado; reproducción queda en su pestaña independiente.
- PDFs de Monitoreo y Animal usan terminología de evento/hallazgo/atención/seguimiento.
- Verificación: migración local aplicada; 209/209 tests, 1360 assertions; navegador comprobó `/monitoreo`, formulario de parásitos, D1–Dn y enlace `BOV26-006` → `/animal/9`.

---

## 2026-08-10 — MONITOREO: FICHA CLÍNICA RÁPIDA

- Ajuste 2026-08-11: eliminado el `<select>` largo de motivos. Ahora se elige una de cuatro rutas visuales (`Detecté`, `Apliqué`, `Realicé`, `Revisé`) y se muestran solo las opciones de esa ruta; al escoger una, el selector se contrae a “Motivo elegido” con acción `Cambiar`. La visibilidad se controla en Livewire (`mostrarSelectorMotivo`) para sobrevivir correctamente a cada re-render.
- El formulario `/monitoreo/sanidad/nueva` se replanteó desde cero como una **ficha continua de campo**, sin tarjetas de categorías ni pasos técnicos repetitivos.
- Entrada mínima: animal(es), fecha y un único **motivo del registro** agrupado por problema observado, aplicación, procedimiento o seguimiento. La categoría, subtipo, severidad y estado se deducen internamente.
- Si hay un problema, solo aparece un triaje de tres niveles (`estable`, `vigilar`, `urgente`), aislamiento y zona corporal cuando corresponde.
- Si se administró un producto, aparece un único bloque con producto, dosis total, vía, número de aplicaciones, intervalo y calendario D1–D6. Las dosis futuras crean recordatorios y se completan desde la tabla existente.
- `observaciones` es el único campo libre clínico y es opcional. Responsable y fotos quedaron dentro de detalles opcionales.
- Migración `2026_08_10_000000_add_withdrawal_fields_to_health_events.php`: agrega `retiro_carne_dias` y `retiro_leche_horas` a `sanidad_registros`; ambos son obligatorios al aplicar producto, con 0 cuando no corresponde.
- No se agregaron dependencias. Se conserva la tabla, historial por `animal_id`, edición, fotos, alertas, PDF y compatibilidad con columnas antiguas.
- Pruebas específicas actualizadas para el flujo mínimo, clasificación automática, multidosis, lotes, alertas, fotos y tiempos de retiro.

---

## 2026-08-11 — MÓDULO MEDICAMENTOS E INVENTARIO SANITARIO

- Nuevo apartado **Operaciones → Medicamentos** con rutas `/medicamentos`, ficha, alta/edición, ingreso de lote y ajuste de existencias. Permisos propios: `medicamentos.{crear,leer,actualizar,eliminar,exportar}`; Administrador y Veterinario reciben gestión completa.
- `medicamentos` sigue siendo el catálogo maestro y ahora guarda: nombre comercial, principio activo, concentración, tipo profesional, presentación, laboratorio, registro SENASA, vía habitual, unidad de inventario, stock mínimo, conservación, foto y observaciones.
- Nueva tabla `medicamento_lotes`: una fila por compra/lote con fecha de ingreso, vencimiento, cantidad inicial/disponible, costo, proveedor, comprobante, ubicación y estado. No se duplica la ficha del producto cuando cambia el vencimiento.
- Nueva tabla `medicamento_movimientos`: libro de inventario con ingreso, aplicación, ajustes, descarte y corrección; conserva lote, animal, dosis sanitaria, usuario, cantidad con signo y saldo posterior.
- La pantalla principal prioriza estado: productos con existencias, próximos a vencer en 30 días y stock vencido. La ficha muestra saldos y una franja visual por lote (`disponible`, `por_vencer`, `vencido`, `agotado`).
- **Aplicar a animal** abre el único formulario `/monitoreo/sanidad/nueva?medicamento={id}` con motivo y producto preseleccionados. No duplica animal, dosis, vía, responsable ni retiro.
- Descuento automático **FEFO**: al marcar una dosis aplicada se consume primero el lote vigente que vence antes. Dosis futuras no reservan ni descuentan. Un lote vencido nunca se usa. Si falta stock vigente, la aplicación se bloquea con mensaje preciso.
- Editar un evento sanitario revierte sus movimientos anteriores, devuelve el saldo al lote y registra la corrección antes de generar el nuevo consumo; evita descuentos duplicados.
- `tratamiento_dosis` añade `cantidad_inventario` y `unidad_inventario`, manteniendo la dosis clínica legible (ej. `5 ml`) y la cantidad contable separada.
- Fotos de medicamentos, lotes y movimientos entran en el backup por fundo; se añadieron verificaciones de integridad para impedir relaciones cruzadas entre fundos.
- Migración aplicada: `2026_08_11_000000_create_medicamento_inventory_module.php`. Pruebas nuevas en `MedicamentosModuleTest`: alta con primer lote, consumo FEFO enlazado al animal y bloqueo de lote vencido.
- **Simplificación visual 2026-08-11**: el alta ya no presenta todos los campos a la vez. El camino principal muestra solo nombre, tipo, presentación, lote, vencimiento y cantidad/unidad; etiqueta, compra administrativa, conservación, foto y observaciones viven en bloques opcionales contraídos.
- Elegir `vacuna` completa automáticamente unidad `dosis` y conservación refrigerada; `antiséptico` propone vía tópica. La presentación también permite inferir tabletas, sobres, dosis, gramos o kilogramos al guardar, sin instalar dependencias.
- `/medicamentos` dejó las cuatro tarjetas grandes y la tabla de seis columnas. Ahora usa una sola franja compacta de estado y tabla de cuatro columnas: Producto, Disponible, Usar primero y Control. Cada fila lleva un riel semántico verde/ámbar/rojo para identificar disponibilidad o riesgo sin leer todo el registro.
- La ficha usa tablas reales y responsivas para **Lotes** (estado, lote, vencimiento, saldo, compra) y **Movimientos** (fecha, movimiento, lote/animal, cambio, saldo). El código del animal en una aplicación enlaza a su ficha.
- `Ingresar lote` pide solo lote, vencimiento y cantidad; fecha, proveedor, costo, comprobante, ubicación y observación quedan opcionales. `Ajustar existencias` oculta la fecha porque hoy es el valor normal.
- Verificación final: Vite y Blade compilan; 10 pruebas específicas verdes (74 assertions); navegador comprobó `/medicamentos`, `/medicamentos/nuevo`, `/medicamentos/5` y `/medicamentos/5/ingreso` sin errores visibles ni formularios expandidos innecesariamente.

---

## 2026-08-12 — FIX UI MÓVIL: BOTÓN "REGISTRAR MEDICAMENTO" & ESCALA TAILWIND 4.5

- **Causa raíz del desborde de icono**: La clase CSS `h-4.5 w-4.5` usada en el `<svg>` del botón "Registrar medicamento" no existía en el tema base de Tailwind v3. Al no aplicarse reglas de alto/ancho en CSS, el SVG caía en su tamaño intrínseco/contenedor en vista móvil, desbordando de forma gigante sobre el botón amarillo.
- **Configuración Tailwind**: Añadida la regla `spacing: { '4.5': '1.125rem' }` en `tailwind.config.js` (`theme.extend`).
- **Ajuste Blade (`resources/views/livewire/medicamentos/index.blade.php`)**:
  - SVG corregido a `h-5 w-5 shrink-0 text-zinc-950`.
  - Botón adaptado a móvil: `w-full sm:w-auto` con flex centrado y texto en `<span>`.
- **Recompilación**: `npm run build` ejecutado exitosamente con Vite 8 (assets CSS actualizados).
- **Diseño Responsivo Tabla `/medicamentos`**:
  - En la vista principal (`index.blade.php`), la tabla de 7 columnas no tenía la envoltura `hidden md:block`. En móviles se mostraba tanto la tabla horizontal con min-width 1020px como las tarjetas inferiores.
  - Fix: Se encerró la tabla HTML en `<div class="hidden overflow-x-auto md:block">` (solo escritorio) y se mejoró la vista de tarjetas `<div class="divide-y divide-zinc-850/60 md:hidden">` agregando miniatura de foto (`<x-table-photo>`) y barra de acciones rápidas móviles (Ver ficha, Ingresar Lote, Editar).
- **Simplificación Extrema Formulario `/medicamentos/nuevo`**:
  - Reducido a solo **4 campos obligatorios**: Nombre comercial, Tipo de producto, Cantidad inicial (con unidad) y Fecha de vencimiento.
  - `presentacion` y `numeroLote` pasaron a opcionales (con fallbacks automáticos `'Envase'` y `'L-1'` si se dejan en blanco).
  - Todos los demás campos (Presentación, N.° Lote, Conservación, Vía habitual, Alerta stock bajo, Foto, Observaciones) se agruparon dentro de un desplegable opcional `<details>`.

---

## 2026-08-12 — MODALES MEDICAMENTOS: EDITAR + LOTE + MOVIMIENTO

**Cambio arquitectónico principal:**
- Las acciones "Editar", "Ingresar lote" y "Ajustar inventario" en `/medicamentos/{id}` ahora se abren como **modales rápidos** en lugar de navegar a páginas separadas.
- Botones del header de `show.blade.php` cambiados de `<a wire:navigate>` a `<button wire:click>`.
- **`Show.php`** absorbió toda la lógica de los 3 formularios:
  - Props separados con prefijos: edit (`nombre`, `tipo`, etc.), lote (`lNumeroLote`, `lFechaVencimiento`, etc.), movimiento (`mTipo`, `mLoteId`, etc.).
  - Métodos: `openEditModal/saveEdit/closeEditModal`, `openLoteModal/saveLote/closeLoteModal`, `openMovimientoModal/saveMovimiento/closeMovimientoModal`.
  - `WithFileUploads` + foto para el modal editar.
  - `lotesDisponibles` inyectado en la vista para el select del modal movimiento.
- **`show.blade.php`**: 3 modales `agro-dialog-overlay` + `agro-dialog` al pie del template (patrón idéntico al usado en `ajustes/backup.blade.php`).

**Tipo de ingreso (compra / donación / depósito):**
- `LoteForm.php` y modal lote de `Show.php` ahora incluyen `$tipoIngreso` con valores `compra|donacion|deposito`.
- El `detalle` del `MedicamentoMovimiento` refleja el tipo: "Compra · Proveedor", "Donación de X", "Depósito de tercero · X".
- Vista standalone `lote-form.blade.php` también actualizada con el selector de 3 opciones (radio pill).

**Rutas mantenidas:** `/medicamentos/editar/{id}`, `/{med}/ingreso`, `/{med}/movimiento` siguen funcionando como páginas completas (fallback para navegación directa). Los botones en show.blade.php invocan modales.

**Tests:** `php artisan test --filter Medicamento` → 4 tests / 24 assertions ✅. Suite completa: verde.

- **Modales en Lista de Medicamentos (`/medicamentos`)**: Los botones de acción de cada fila de la tabla principal y tarjetas móviles (los iconos verdes de "Ingresar lote" y amarillos de "Editar" que antes navegaban a páginas separadas) ahora abren los **modales directamente desde el listado** (`openLoteModal($id)` y `openEditModal($id)`).

## 2026-08-12 — REVISIÓN Y FIX MÓDULO MEDICAMENTOS + DISEÑO FORMAL (sesión posterior)

**ACLARACIÓN DE ESTADO ACTUAL (corrige la sección anterior):** hoy SOLO el modal de "Reabastecer stock" (lote) es modal. "Editar ficha" es `<a wire:navigate>` a la página `/medicamentos/editar/{id}`, y "Ajustar inventario" usa la página `/medicamentos/{id}/movimiento`. NO existen `openEditModal`/`openMovimientoModal` en el código actual (se revirtió a páginas separadas); `Show.php` solo maneja el modal de lote.

**Fixes aplicados en esta sesión:**
- **BUG CRÍTICO `Show.php` (modal de la ficha nunca funcionó)**: usaba las propiedades `l*` del modal (`lTipoIngreso`, `lCantidad`, etc.) en `openLoteModal()/saveLote()` SIN declararlas. Livewire 3 NO sincroniza propiedades no declaradas vía `wire:model` → consola: "property does not exist on component" + "Livewire Entangle Error". FIX: declarar `public string $lTipoIngreso='compra'; public string $lNumeroLote=''; ... public int|float|string $lCantidad=''; ...` (igual que `Index.php`). LECCIÓN: TODA propiedad usada en `wire:model`/`@entangle` DEBE declararse en la clase.
- **COMPONENTE COMPARTIDO `components/medicamento-lote-modal.blade.php`**: el modal estaba DUPLICADO (~110 líneas) en `index.blade.php` y `show.blade.php`. Extraído a componente con props `:nombre`, `:unidad`, `:wire-submit` (default `saveLote`). Uso: `<x-medicamento-lote-modal :nombre="$medicine->nombre" :unidad="$medicine->unidad_label" />` dentro de `@if($showLoteModal)`.
- **ENLACE ROTO desde Finanzas**: `finanzas/movimiento-form.blade.php` enlazaba a `route('medicamentos.index')?action=nuevo-lote` pero NADIE procesaba `action`. FIX: `mount()` en `Medicamentos/Index.php` — si `request()->query('action')==='nuevo-lote'` → `openLoteModal($first->id)` (primer medicamento activo) o toast guía. VERIFICADO navegador: abre el modal con el producto preseleccionado.
- **`</div>` EXTRA al final** de `index.blade.php` y `show.blade.php` (HTML desbalanceado). Eliminado.
- **Unificación radio**: `lote-form.blade.php` usaba `deposito` para "Saldo Inicial" mientras el modal usa `saldo_inicial`. Unificado a `saldo_inicial`.
- **Campos re-agregados y ELIMINADOS de nuevo** (error mío): había añadido `principioActivo`, `concentracion`, `presentacion` a la vista del form → el usuario: "esos ya no deberían de estar" → eliminados de `form.blade.php` (quedan como props en `Form.php` y se guardan). Regla: NO re-agregar a la vista campos que el rediseño anterior ocultó; los tests los setean directo al backend sin DOM.
- **Test regresión NUEVO** `test_show_modal_lote_fields_are_bound_and_save_a_new_lot`: abre modal en Show, setea lCantidad/lCostoTotal/lProveedor (compra requiere costo), saveLote → verifica lote + movimiento + egreso financiero. Suite completa 215/215 (1399 assertions).

**DISEÑO DEL FORM `medicamentos/form.blade.php` — evolución y decisión final:**
1. Queja 1: "está feo peor negro" → el panel era `dark:bg-zinc-900` plano.
2. Intento con gradientes por sección (ámbar/esmeralda/zinc) → RECHAZADO: "sin degradados, hazlo más formal, más elegante".
3. DECISIÓN FINAL (aceptada): diseño FORMAL sin degradados — tarjetas planas `rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900` por sección (Datos principales / Existencia inicial / Más detalles), iconos NEUTROS en caja (`bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400`), títulos `uppercase tracking-wider`, subtítulos descriptivos, divisorias `border-b`, botón guardar ámbar SÓLIDO (`bg-amber-500 hover:bg-amber-400`), panel foto plano.
- **LECCIÓN DISEÑO**: el usuario RECHAZA (a) fondos planos negros sin estructura y (b) degradados en paneles. ACEPTA: superficies planas claras, secciones con divisorias, iconos neutros, acento de color SOLO en CTAs/foco.
---

## 2026-08-12 / 2026-08-13 — FIXES MODALES MEDICAMENTOS, CONCURRENTLY Y AUDITORÍA DE FINANZAS

- **Medicamentos - Sincronización de `deposito` en Modales Lote**:
  - `Show.php` e `Index.php` actualizados con `Rule::in(['compra', 'donacion', 'deposito', 'saldo_inicial'])` y `match ($this->lTipoIngreso)` contempla `'deposito' => 'Depósito de tercero'`.
  - Componente `medicamento-lote-modal.blade.php` incluye la 4ta opción radio `['deposito' => ['🏦', 'Depósito']]` con grid adaptado `grid-cols-2 sm:grid-cols-4`.
- **Desarrollo - Eliminación de `--kill-others`**:
  - Removido `--kill-others` de `composer.json` en `composer run dev` (`npx concurrently`), evitando que un timeout/excepción puntual en `queue:listen` o `schedule:work` cierre Vite y el servidor PHP.
- **Finanzas - Fixes en Edición de Movimientos (`MovimientoForm.php`)**:
  - Fix tipo `categoriaId`: `mount()` castea a `(string) $mov->categoria_id` para coincidencia de tipo con `x-filter-select`.
  - Fix `proposito` huérfano: al editar registros no-asignación (ej. Medicamentos), `proposito` y `beneficiario` se resetean a defaults válidos (`'estudio'` / `''`), evitando fallos en `Rule::in` si el usuario cambia la categoría a Asignación Familiar.
- **Limpieza de repositorio y Git**:
  - `.gitignore` actualizado con `/refactor.py`, `/test-results`, `/.playwright-mcp` y `/storage/framework/sessions`.
  - Suite de pruebas verificada en verde: `215/215` tests pasados (1399 assertions).

---

## 2026-08-13 — COMPRA VETERINARIA UNIFICADA: MEDICAMENTOS + FINANZAS + FOTO

- **Causa raíz corregida:** `medicamento_lotes` y `movimientos` duplicaban una compra sin FK. Por eso editar medicamento no mostraba precio/lote, editar Finanzas no mostraba inventario completo y ambos podían divergir.
- Migración aplicada: `2026_08_13_000000_link_medicine_purchases_to_finance.php`. Añade `medicamento_lotes.movimiento_id` (FK única nullable, `nullOnDelete`) y enlaza compras históricas por fundo + fecha + monto + categoría, priorizando nombre/lote. Datos reales verificados: lote 13 `ENT-002` quedó unido al movimiento 14; lote 12 `ENT-001` al 13.
- Servicio único nuevo `MedicamentoPurchaseService`: crea/actualiza lote, movimiento de inventario y egreso financiero dentro de transacción. Los cuatro puntos de ingreso (`Medicamentos/Form`, `LoteForm`, modal `Index`, modal `Show`) usan este servicio; se eliminó lógica financiera duplicada.
- `/medicamentos/editar/{id}` ahora carga el lote más reciente y permite elegir otro. Muestra número, fechas, cantidad, unidad, costo total, proveedor, referencia y ubicación. En compras vinculadas, guardar actualiza el mismo egreso financiero y conserva el consumo ya realizado; no permite bajar la cantidad inicial por debajo de lo consumido.
- `/finanzas/movimiento/nuevo` y `/finanzas/movimiento/editar/{id}`: al elegir **Medicamentos → Para animales (Inventario)** ya NO redirige. Despliega medicamento, cantidad, lote, vencimiento, proveedor, referencia y ubicación, manteniendo monto, fecha, descripción, foto y botón guardar.
- **Foto única compartida:** la fuente de verdad sigue siendo `medicamentos.foto_ruta` + `foto_encuadre`. Finanzas no copia la imagen: tabla, detalle y formulario leen la foto por `Movimiento -> compraMedicamento -> medicamento`. Cambiarla desde cualquiera de los dos formularios actualiza ambos módulos.
- Tabla Finanzas ahora muestra “Foto / comprobante”, etiqueta medicamento+lote y permite buscar por nombre/lote. El filtro con/sin comprobante también considera la foto compartida.
- `/medicamentos` optimizado: resumen de lotes pasó de tres consultas estadísticas a una consulta condicional agregada; los cuatro KPIs grandes se cambiaron por una franja compacta formal.
- Validación automatizada: `217/217` pruebas, `1433` aserciones. Test nuevo cubre alta desde Finanzas, enlace único, edición desde ambos módulos y cambio de foto bidireccional.
- Validación real en navegador: `/medicamentos/editar/21`, `/finanzas/movimiento/editar/14`, `/finanzas/movimiento/nuevo`, `/finanzas?tab=movimientos` y `/medicamentos`. Confirmados campos completos, foto de `desalor` en Finanzas y flujo inline sin guardar datos durante inspección.

---

## 2026-08-13 — CÓDIGO ANUAL ÚNICO PARA LOTES DE MEDICAMENTOS

- El campo se llama **Código de medicamento** (antes "Código del lote"). Formato obligatorio: `MET` + año de 2 dígitos + `-` + consecutivo anual de 3 dígitos; ejemplo: `MET26-001`.
- El prefijo y el año son fijos. En alta y edición el usuario solo puede modificar los últimos 3 dígitos; entradas como `7` se normalizan a `007`.
- Fuente única `MedicamentoLotCodeAllocator`: secuencia global por fundo y año, validación de rango `001..999`, detección de duplicados y asignación transaccional segura. No se reinicia por producto.
- Alta automática robusta: si dos formularios abiertos muestran `001`, el primero guarda `MET26-001` y el segundo obtiene `MET26-002` dentro de la transacción.
- Componente compartido `medicamento-lot-code-input.blade.php`, basado en el patrón visual del código animal. Se usa en alta/edición de medicamento, modal de reabastecimiento, ingreso independiente y alta/edición desde Finanzas.
- Migración aplicada: `2026_08_13_010000_standardize_medicine_lot_codes.php`. Crea `medicamento_lot_code_sequences`, cambia la unicidad a fundo+código y convierte lotes históricos preservando orden anual.
- Datos reales: lote 12 → `MET26-001`; lote 13 / movimiento 14 → `MET26-002`. El texto financiero vinculado también fue actualizado.
- Verificación: pruebas focalizadas 12/12 (153 aserciones); build Vite y caché Blade correctos. En navegador, `/medicamentos/nuevo` sugirió `MET26-003`; las ediciones 21 y 14 mostraron `MET26-002`, sufijo `002`, precio/campos/foto sincronizados, sin errores de consola.
- **Sincronización de Eliminación (Finanzas ↔ Medicamentos)**: `MedicamentoPurchaseService::deleteLinkedMovement()` y `deleteLot()`. Eliminar un egreso financiero en `/finanzas` sin consumo en botiquín borra atómicamente el lote e ingreso en inventario. Si el lote ya tiene dosis aplicadas a animales, el lote se preserva (`movimiento_id = null`) para mantener la trazabilidad clínica animal. Eliminar un lote sin consumo en `/medicamentos` elimina simultáneamente el egreso financiero en `/finanzas`. Pruebas en `MedicineFinanceDeleteSyncTest` (5/5 verde).
- **Eliminación opción "Depósito"**: Se eliminó la opción `deposito` de todos los formularios y modales de medicamentos (`Form`, `LoteForm`, `Show`, `Index`, `medicamento-lote-modal.blade.php`). Los únicos 3 motivos de entrada válidos son `compra`, `donacion` y `saldo_inicial` con grid uniforme de 3 columnas.
- **Historial de Aplicaciones a Animales**:
  - En `/medicamentos` se integró navegación por pestañas: `📦 Inventario y Botiquín` vs `💉 Historial de Aplicaciones a Animales`.
  - La tabla de aplicaciones detalla: Fecha/Hora, Animal (arete, nombre, especie), Medicamento/Insumo, Código de lote (`MET26-XXX`), Dosis/Cantidad descargada con unidad, Diagnóstico/Evento de salud (caso clínico o preventivo) y Responsable.
  - Filtros colapsables integrados para aplicaciones: búsqueda por arete/nombre/lote, filtro por producto y selector de rango de fechas (desde / hasta) con paginación independiente.
  - En `/medicamentos/{id}` (`Show`) se añadió la tabla dedicada de aplicaciones específicas realizadas con ese producto.
- **Botiquín Unificado en Operaciones (`/medicamentos` y `/medicamentos/{id}`)**:
  - Menú de navegación unificado bajo **Botiquín** (subtítulo: `Medicamentos e insumos`), eliminando enlaces duplicados para mayor limpieza y rapidez.
  - Navegación interna por 3 pestañas dinámicas en `/medicamentos`:
    1. `💊 Medicamentos`: Catálogo clínico, stock disponible, lotes `MET26-XXX`, alertas de vencimiento y criterio FEFO.
    2. `🧤 Insumos y Materiales`: Materiales descartables, gasas, algodón, agujas, jeringas, alcohol, lotes `INS26-XXX`. Fecha de vencimiento 100% opcional (marcado automático como *No perecible* si no tiene).
    3. `💉 Historial de Aplicaciones`: Historial completo de aplicaciones sanitarias y dosis en animales (sin botón de creación en cabecera).
  - **Ficha Individual de Producto (`/medicamentos/{id}` e `/insumos/{id}`)**:
    - Tabla de existencias/lotes con CRUD individual: `+ Nuevo Lote`, `Ver egreso en Finanzas` y `Eliminar lote` (con confirmación y borrado financiero).
    - Tabla de historial de aplicaciones con CRUD individual: `Ver animal` y `Eliminar aplicación` (revirtiendo stock automáticamente al lote).
    - Selector "Mostrar [5, 10, 20, 50] registros" + paginación estándar (`agro-table-footer`) en ambas tablas independientes.
  - **Arquitectura de Navegación y Navbar Estandarizado (`components/navbar.blade.php`)**:
    - **Menú Ganadería**: `Animal` (fichas e inventario), `Engorde` (lotes y pesajes), `Monitoreo` (sanidad, partos y celos), `Botiquín` (medicamentos e insumos).
    - **Menú Producción**: `Leche` (ordeño diario), `Queso` (elaboración y derivados), `Finanzas` (ingresos y egresos).
    - **Menú Sistema**: `Ajustes` (usuarios y configuración), `Auditoría` (trazabilidad y logs), `Gestión Web` (landing pública).
    - **Acceso Directo Rápido en Navbar**: Botón directo de **Buscador global** (`🔍 Buscar` con atajo `/`), enlace directo a **Sitio Web**, switch de tema Dark/Light y menú de usuario.
    - **Ajuste Responsivo Fluido**: Optimización de anchos y paddings para pantallas medianas (1024px-1366px); badge de fundo truncado adaptable (`hidden xl:flex`), avatar compacto sin overflow y cero scroll horizontal.
    - **Buscador (`/buscador`)**: Incorporación de botón inteligente **"Atrás"** (`window.history.back()` con retorno al dashboard).
    - **Finanzas: Tarjeta MÁS DETALLES en Medicamentos e Insumos**:
      - Integración completa de la tarjeta de inventario: `Conservación`, `Vía habitual`, `Alerta stock bajo` (con unidad dinámica) y `Observaciones`.
      - Sincronización bidireccional entre Finanzas y Botiquín (Medicamentos e Insumos) con creación y persistencia automática de compras, egresos y lotes.
      - Sincronización bidireccional total entre Finanzas (`/finanzas/movimiento/editar/{id}`) y Botiquín (`/insumos/editar/{id}` y `/medicamentos/editar/{id}`): compras, egresos, lotes `INS26-XXX` / `MET26-XXX`, selector de lotes editables, fechas de vencimiento (opcionales para descartables, requeridas para medicamentos), tarjetas "Más Detalles" y sincronización automática de fotos y encuadres con el disco `public`.
      - Enlaces y miniaturas interactivos en la tabla de Finanzas hacia `/insumos/{id}` y `/medicamentos/{id}` con encuadre de imagen (`ImageFrame`).
      - Sincronización bidireccional automática de imágenes: fotos subidas en Finanzas como comprobante de compra de insumo se reflejan de inmediato en `/insumos/editar/{id}` y `/insumos/{id}`, y viceversa.
    - **Reglas de Oro y Convenciones Estandarizadas de AgroFundo (2026)**:
      1. **Comunicación**: Modo `/caveman ultra` activo por defecto (respuestas ultra comprimidas, sin saludos ni relleno, tablas y listas directas).
      2. **Diseño Visual y Componentes UI**: Superficies planas formales (`bg-white` / `dark:bg-zinc-900`), sin degradados de texto ni fondos estridentes. Barra de pie de tabla estándar (`agro-table-footer`) con `x-filter-select` y paginación. Componente `x-filter-select` con buscador opcional explícito (`searchable`), foco sincronizado con la paleta de color y ancho adaptativo (ancho del trigger para selects compactos, 300px+ solo si es `searchable`). Acciones de fila (`<x-table-action>`) con SweetAlert (`swal:confirm`). Subida de fotos unificada estilo Queso en todos los formularios (`optimizedImageUpload` / `optimizedAttachmentUpload` y `ImageOptimizer::store()`): área de drop/click directo con `<label>` clickeable al 100%, previsualización instantánea de 0ms con `URL.createObjectURL()`, compresión en segundo plano a WebP, barra de progreso animada, editor de encuadre/foco (`<x-image-frame-editor>`), botones adaptativos de captura/galería (`<x-image-source-actions>`) y compatibilidad total con modo claro y modo oscuro. Botón "Atrás" inteligente: `window.history.length > 1 ? window.history.back() : window.location.href='{{ route('dashboard') }}'`.
      3. **Arquitectura del Navbar**: Ganadería, Producción y Sistema organizados jerárquicamente; responsive fluido sin scroll.
      4. **Módulo Botiquín, Monitoreo y Finanzas**: Fármacos `MET26-XXX` (FEFO) / Insumos `INS26-XXX` (vencimiento opcional: antisépticos/alcohol/yodo sí tienen caducidad, descartables/gasas/instrumental pueden quedar vacíos sin vencimiento). Categoría global `Insumos y Materiales` en `categorias_financieras` (15 categorías base) con tono Emerald distintivo, icono de suministros y formulario de ingreso directo de nuevo insumo/medicamento en Finanzas (`MovimientoForm`) sin botones de modo ni datos previos que recuperar. Sincronización bidireccional total en eliminaciones: borrar un Insumo o Medicamento elimina en cascada sus lotes, movimientos de inventario y egresos en Finanzas (`movimientos`); borrar un egreso en Finanzas elimina el lote vinculado si no tiene consumo (o lo desvincula si ya fue usado en sanidad/campo); borrar un lote sin consumo elimina su egreso en Finanzas. Sistema avanzado de filtros en Botiquín (`/medicamentos` e `/insumos`): componentes `<x-collapsible-filters>` formales con soporte Dark/Light mode, badges de filtros activos, filtros de fecha/vencimiento por día/mes/año con presets rápidos (`30d`, `60d`, `Este año`, `Hoy`, `Esta semana`, `Este mes`), selectores de ordenación múltiple (`Nombre A-Z / Z-A`, `Stock`, `Próximo a Vencer`) y botón de reseteo instantáneo. Exportación de reporte PDF integral y dinámico en Botiquín con selección múltiple de secciones (`Medicamentos`, `Insumos`, `Historial de Aplicaciones`, `Consumos`) y columnas personalizables con select-all, formato A4 horizontal DejaVu Sans con métricas resumen y sincronización con todos los filtros activos. Placeholders de ayuda en todos los inputs (`Ej. 10`, `Ej. Farmavet`, `Ej. 3M / Nipro`, `Ej. 5`, etc.). Formateo limpio de números con `formatNumber()` (enteros sin `.000`, máx 2 decimales si aplica). Compras de medicamentos e insumos en Finanzas muestran badges interactivos clicables con foto miniatura y enlace directo a su ficha. Matriz de roles en Ajustes con módulo claramente identificado como `Botiquín e Insumos`.
        - **3 Categorías Clave con Color e Icono Único en Selects Financieros (`x-filter-select`)**:
          1. `Asignación Familiar`: Color Violeta (`text-violet-700 dark:text-violet-300`, `bg-violet-600`) + Icono Persona/Familia (`👤`).
          2. `Insumos y Materiales`: Color Esmeralda (`text-emerald-700 dark:text-emerald-300`, `bg-emerald-600`) + Icono Caja de Suministros/Paquete (`📦`).
          3. `Medicamentos`: Color Cian (`text-cyan-700 dark:text-cyan-300`, `bg-cyan-600`) + Icono Frasco de Medicamentos con Cruz Médica (`💊`).
      5. **Dashboard Principal (`/dashboard`)**:
         - **Centro de Operaciones Unificado**: Superficie formal plana (`bg-white` / `dark:bg-zinc-900`) con soporte Dark/Light mode al 100%.
         - **Lectura Rápida en Vivo**: Métricas clave en tiempo real: Ordeño hoy (L), Saldo de mes (S/), Alertas sanitarias (conteo y vencidas), y Botiquín (total fármacos/insumos + alertas de stock bajo y lotes por vencer en 30 días).
         - **Acciones Rápidas Unificadas**: Atajos directos a creación de Animal, Ordeño, Movimiento Financiero, Parto, Atención Sanitaria, Queso, Medicamento e Insumo.
         - **Resumen Operativo (7 Tarjetas KPI)**: Animales en inventario, Leche producida (con variación mensual y promedio 7d), Queso producido (con variación y unidades), Finanzas (balance y desglose), Botiquín e Insumos (fármacos + insumos + alertas de stock), Engorde (lotes y activos), y Monitoreo (alertas, partos y atenciones sanitarias del mes).
         - **Gráficos Dinámicos e Interactivos**:
           - *Tendencia Productiva*: Selector Leche/Queso, rangos 6/12 meses, curva SVG con área suave y selector interactivo por mes con métricas detalladas en aside.
           - *Control Financiero*: Barras interactivas Ingresos vs Egresos (6/12 meses) con hover, selección de mes y cálculo de balance.
           - *Composición del Inventario*: Gráfico cónico tipo donut por especie con filtro y porcentajes interactivos.
         - **Atención Requerida y Directorio de Módulos**: Lista de alertas con badges de estado y mapa de navegación organizado en *Ganadería y Producción* y *Suministros y Control*.
       6. **Confirmaciones de Eliminación Estandarizadas (`swal:confirm` + `swal:modal`)**:
          - **Previo a toda eliminación**: SweetAlert modal de confirmación (`swal:confirm`) con título formal `¿Estás seguro?`, descripción clara del elemento a retirar/eliminar/archivar, y botones `Sí, eliminar` / `Sí, retirar` vs `Cancelar`. Prohibido borrar directamente con `wire:click="delete..."` sin confirmación previa.
          - **Dentro de Lotes de Engorde**: El retiro de animales del lote pasa por confirmación explícita con nombre/arete (`solicitarQuitarAnimal`) antes de ejecutar `quitarAnimal`.
          - **Post-eliminación**: Notificación mediante SweetAlert Modal (`swal:modal` con icono `success` y título `Eliminado` o `Ejemplar Retirado`) en lugar de toasts efímeros donde se requiera confirmación explícita.
       7. **Resiliencia del Dashboard y Prevención de Error 500**:
          - `Dashboard.php` autogestiona la recuperación del `fundo_id` desde los fundos activos del usuario si la sesión estuviera vacía.
          - Vista `<x-global-dashboard>` incluye guardia de fallback seguro ante arrays vacíos, previniendo cualquier excepción de tipo `Undefined array key`.
          - Consultas SQL directas respetan estrictamente el esquema real de MySQL: `sanidad_registros.fecha_evento` (no `fecha_registro`) y `whereNull('deleted_at')` únicamente en tablas con `SoftDeletes` (`medicamentos` e `insumos` se gestionan por `activo = 1`).
       8. **Pruebas y Calidad**: Suite 252/252 tests pasando (1,676 aserciones al 100%). Build de Vite verificado (`npm run build`). Servidor de desarrollo unificado (`composer run dev`).

## 2026-08-16 — VERIFICACIÓN PROFUNDA DEL SISTEMA (suite verde + hallazgo de código muerto)

- **Estado confirmado**: `php artisan test` → 252/252 tests, 1,676 assertions, todo en verde. Sin errores de runtime.
- **`get_errors` del IDE = FALSOS POSITIVOS** (no son bugs): los avisos "Undefined method user/check/id" en `auth()` son por la resolución de tipos del helper; "Not enough arguments" en Eloquent (`where()->firstOrFail()`) y `Storage::disk()->assertExists()` son métodos mágicos/facade que Intelephense no resuelve. El `$backup->delete()` de `FundoDatabaseBackupService` es el `delete()` de Eloquent (sin args), confundido con el método `delete(DatabaseBackup, Fundo)` del servicio. NO tocar por estos avisos.
- **Refactor ya hecho (confirmado)**: la lógica de guardado de lotes/ingresos de medicamentos NO está duplicada; se centralizó en `MedicamentoPurchaseService` y `InsumoPurchaseService`. Los componentes Form/Index/Show/LoteForm los inyectan por parámetro. Módulo Insumos completo (modelos Insumo/InsumoLote/InsumoMovimiento + migraciones + vistas).
- **HALLAZGO — CÓDIGO MUERTO / "por gusto" en Finanzas (bajo impacto, no rompe)**:
  1. La pestaña **'asignaciones'** de `Finanzas/Index.php` tiene backend vivo pero SIN UI: props de filtro (`searchAsignacion`, `propositoAsignacion`, `periodoAsignacion`, `fechaDesdeAsignacion`, `fechaHastaAsignacion`, `montoMinAsignacion`, `montoMaxAsignacion`, `conFoto`), `ASSIGNMENT_FILTERS`, `REPORT_SECTIONS['asignaciones']`, `REPORT_COLUMNS['asignaciones']`, listener `confirmarEliminacionAsignacion`→`deleteAsignacion`, `recentRecordScopes['finanzas.asignaciones']` y claves de queryString. La vista `finanzas/index.blade.php` SOLO renderiza `$movimientos` (no hay selector de tabs). Las asignaciones ya viven dentro de movimientos (categoría "Asignación Familiar").
  2. `AsignacionForm.php`, `AsignacionShow.php`, `asignacion-form.blade.php`, `asignacion-show.blade.php` + rutas `finanzas.asignacion.edit`/`finanzas.asignacion.show` están **huérfanos** (sin entrada desde la UI; se enlazan entre sí).
  3. `AsignacionFamiliar.php` + tabla `asignaciones_familiares` siguen existiendo (backup/borrado las incluyen) pero sin pantalla propia.
  - DECISIÓN (heredada de sesiones previas): se mantienen como código latente para no romper tests/referencias/PDF. Si se quiere limpiar, hacerlo en una tarea aparte: quitar routes + componentes + vistas + props de filtro + claves queryString, y verificar que `FinanzasModuleTest`/`RecentRecordModulesTest`/PDF `finance-index.blade.php` no referencien 'asignaciones'.

## 2026-08-16 — LIMPIEZA EJECUTADA del código muerto de asignaciones (suite 252/252 verde)
- ELIMINADO:
  - Archivos: `app/Livewire/Finanzas/AsignacionForm.php`, `AsignacionShow.php`, `resources/views/livewire/finanzas/asignacion-form.blade.php`, `asignacion-show.blade.php`.
  - Rutas: `finanzas.asignacion.edit`, `finanzas.asignacion.show`, `asignacion.foto` (SE MANTIENE `finanzas.asignacion.create` como redirect a `finanzas.movimiento.create` por compatibilidad).
  - `SecureFileController::showAsignacionFoto()` + import `AsignacionFamiliar`.
  - En `Finanzas/Index.php`: prop `$tab`, props de filtro de asignaciones, `ASSIGNMENT_FILTERS`, `REPORT_SECTIONS['asignaciones']`, `REPORT_COLUMNS['asignaciones']`, listener `confirmarEliminacionAsignacion`, métodos `solicitarEliminacionAsignacion`/`deleteAsignacion`/`assignmentQuery`/`updatedTab`/`resetAsignacionFilters`/`updated*Asignacion`, claves queryString de asignaciones + `tab`, rama `else` de `buildFinanceReport` y `recentRecordScopes['finanzas.asignaciones']`.
- SE CONSERVA (correcto): el modelo `AsignacionFamiliar` + tabla `asignaciones_familiares` (legacy, usada por `UppercasePersistenceTest`, `HasDangerZoneManagement` y `FundoDatabaseBackupService`), y la categoría "Asignación Familiar" que ahora vive dentro de los movimientos (`esCategoriaAsignacionFamiliar`, `dashboardData`).
- `HasRecentRecord` usa `property_exists($this,'tab')` antes de setear `$this->tab`, por lo que es seguro haber eliminado la prop.
- Test actualizado: `RecentRecordModulesTest::test_created_and_updated_assignment...` ya no aserta `tab` (se eliminaron las 2 líneas `assertSet('tab', ...)`).
- VERIFICADO: `php artisan test` → 252/252 (1,674 assertions).

## 2026-08-16 — ZONA DE PELIGRO CON CONTEO EN VIVO y DESGLOSE TRANSPARENTE (suite 252/252 verde)
- Reporte usuario: "la zona de peligro no se actualiza con los nuevos datos" + "no tengo esa cantidad de información". Diagnóstico:
  - La vista mostraba lista estática → ahora `dangerZoneCounts()` calcula **en vivo** desde MySQL por `fundo_id` de sesión.
  - Los números mezclaban entidades + sub-registros (ej. "Animales y engorde 36" = 17 animales + 4 lotes + 9 en lotes + 6 pesajes), por eso el usuario percibía cifras infladas.
- `HasDangerZoneManagement::dangerZoneCounts(): array` devuelve `{total, records, files, groups[]}` donde cada `group` tiene `{label, total, items[]}` con desglose por sub-partida (solo muestra items > 0):
  - Animales y engorde: Animales / Lotes de engorde / Animales en lotes / Pesajes registrados.
  - Ordeños y leche: Ordeños / Detalles de ordeño.
  - Producción de queso: Producciones / Presentaciones.
  - Finanzas y asignaciones: Movimientos / Asignaciones familiares.
  - Monitoreo: Registros de sanidad / Dosis de tratamiento / Partos / Alertas programadas.
  - Botiquín: Lotes de medicamentos / Movimientos de medicamentos / Lotes de insumos / Movimientos de insumos.
- NUEVO `operationalFilesCount()`: cuenta archivos ÚNICOS reales (fotos/evidencias/comprobantes) vía `collectOperationalFiles()` → fila "Fotos, evidencias y archivos adjuntos" ahora muestra número.
- Vista `danger-zone.blade.php`: badge del total muestra `records` (registros operativos) con tooltip del total real (`records + files`); cada grupo con subtotal + partidas indentadas; fila final de archivos con su conteo.
- BUG REAL corregido: `FundoDatabaseBackupService` no borraba las tablas de insumos (`insumos`, `insumo_lotes`, `insumo_movimientos`) en `deleteFundoData`. Añadidas a `tableQueries()` + `MIXED_TABLES` + `'insumos' => 'foto_ruta'` en `$publicTables` de `collectOperationalFiles`.
- VERIFICADO: navegador muestra desglose correcto (ej. Animales y engorde 36 → 17/4/9/6; Monitoreo 45 → 18/20/4/3; Fotos 38). `php artisan test` → 252/252 (1,674 assertions).

## 2026-08-16 — ZONA DE PELIGRO "SE CONSERVA" + BACKUPS EN VIVO (suite 252/252 verde)
- "Se conserva" de la zona de peligro ahora es DINÁMICO: `HasDangerZoneManagement::dangerPreservedCounts()` cuenta en vivo Usuarios con acceso (`fundo_user`), Roles y permisos (globales + del fundo), Configuración del sistema (`configuracion_sistema`), Identidad visual (`branding_settings`) y Backups existentes (`database_backups`). La vista muestra cada uno con badge numérico (antes era lista estática).
- Pestaña **Backups** ahora muestra "Contenido actual a respaldar" en vivo (coherente con la zona de peligro): reusa `dangerZoneCounts()` en `backup.blade.php` → badge `X reg. · Y arch.` + desglose por módulo. Nota: "Coincide con lo que se eliminaría en la Zona de peligro."
- Detalle: `database_backups` se cuenta con `fundo_id` (scope `forFundo`); `branding_settings` es singleton global (id=1).
---

## 2026-08-17 — AUDITORÍA PROFUNDA Y MEJORA INTEGRAL (suite 252/252 verde, build Vite OK)

- **Fase 1 (Colores y Modo Claro/Oscuro)**:
  - Eliminado uso de `slate-*` (gris frío no mapeado) en componentes clave (`animal-multi-select`, `collapsible-filters`, `date-picker`, `filter-select`, `auditoria/index`) migrado a `zinc-*` (mapeado a paleta andina cálida).
  - Corregidos badges oscuros `-950` sin variante `dark:` en `medicamentos/index` y `medicamentos/show` — ahora usan patrón dual `bg-rose-100 dark:bg-rose-950/80`.
  - Corregido texto blanco invisible en modo claro sobre `bg-zinc-950` en `medicamentos/show` y `monitoreo/sanidad-form` → `bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200`.
  - Corregida clase inexistente `text-zinc-350` en `leche/show` → `text-zinc-400`.
- **Fase 2 (Unificación de Tablas & UI/UX)**:
  - Unificadas tablas de `insumos/index`, `insumos/show` y `ajustes/user-security` al patrón semántico C (`agro-record-surface`, `agro-record-header`, `agro-record-list`).
  - Creado componente reutilizable `components/empty-state.blade.php` con SVGs dinámicos e integrado en `insumos/index`, `medicamentos/index`, `auditoria/index`.
  - Botones de acción móvil en `medicamentos/index` e `insumos/index` adaptados con colores duales claro/oscuro.
  - Paginación de Insumos envuelta con `.agro-table-footer` y `components.pagination`.
- **Fase 3 (Traits y Reutilización)**:
  - Creado `App\Traits\HasCsvExport` (`streamCsv` con BOM UTF-8 y headers unificados).
  - Creado `App\Traits\HasSorting` (`toggleSort` y `applySorting` para tablas Livewire).
- **Fase 4 (Performance & Optimización de Carga)**:
  - `Medicamentos/Index.php`: render optimizado con carga condicional por pestaña activa (`$tab === 'inventario' | 'insumos' | 'aplicaciones'`), evitando ejecutar 6 queries innecesarias por render (~66% reducción de carga SQL).
  - `Monitoreo/Index.php`: agregaciones mensuales y estadísticas de categorías cacheadas por 2 min con `Cache::remember`.
  - `Finanzas/Index.php`: `dashboardData` cacheado por 2 min con `Cache::remember`.
- **Fase 5 (Correcciones de Rutas y Seguridad)**:
  - `RecordActivity.php`: mapeadas rutas de `medicamentos` e `insumos` a sus respectivos módulos en auditoría (antes caían en `cuenta`).
  - `profile/delete-user-form.blade.php`: corregido `forceDelete()` → `delete()` para respetar `SoftDeletes` del modelo `User`.
  - Tests actualizados y determinísticos: `ProfileTest` (`assertSoftDeleted`) y `AuditAndSessionsTest` (fijado a día hábil con `Carbon::setTestNow`).
- **Fase 6 (Vida Visual & Sort Headers)**:
  - Creado `<x-table-sort-header>` con SVGs nítidos y micro-animaciones hover. Reemplazados triángulos unicode en `animal/index` y `leche/index`.
- **Corrección Zona de Peligro & Gestión Web / Backups**:
  - **Borrado total web**: `HasDangerZoneManagement` ahora incluye `landing_blocks` y `media` (fotos públicas y carrusel) tanto en el conteo en vivo (`dangerZoneCounts`), como en la recolección de archivos físicos (`collectOperationalFiles`) y en el borrado atómico (`deleteWebData` borra registros y directorios de almacenamiento).
  - **Backup previo de seguridad**: corregido `components: ['web' => true]` en el backup previo de la zona de peligro para que conserve todo el contenido web antes del borrado y permita restauración 100% íntegra.
- **Unificación Estética y Neutralización de Tokens**:
  - **Tokens de tema neutrales**: Eliminado el tinte verdoso forzado en fondos (`--bg-primary: #f8fafc;`), bordes (`--border-primary: #e2e8f0;`) y cabeceras (`--bg-tertiary: #f1f5f9;`). Todos los inputs y selects ahora usan bordes neutros limpios y no verdes por defecto.
  - **Formulario de Registro de Ordeño (`/leche/nuevo`)**:
    - **Bloque superior nivelado**: Nivelación milimétrica de baseline y alturas (`h-11` estándar en `<x-date-picker>` y `<x-filter-select>`, contenedores de etiquetas con altura bloqueada `h-5`).
    - **Acentos y Vida Visual Dual-Mode**:
      - Borde superior temático de acento por tarjeta (`border-t-4 border-t-emerald-500`, `border-t-teal-500`, `border-t-sky-500`, `border-t-amber-500`).
      - Insignias dinámicas de turno con reactividad en tiempo real (0ms con Alpine `$wire.entangle('turno').live`) y prop `live` en `<x-filter-select model="turno">` (Mañana: ámbar, Tarde: cielo, Noche: índigo).
      - Chips de identificación con micro-sombras e insignias de unidad en cajas badge (`L`, `vacas`).
      - Textos de alta legibilidad y contraste dual (`text-zinc-900` / `dark:text-zinc-100`, con secundarios `text-zinc-600` / `dark:text-zinc-300`).
      - Mensajes de validación y banners con tonos suaves armónicos.
      - Banner de asistencia contextual activo y estilizado.
    - **Acciones y footer**: Botones `.agro-button` y `.agro-button-secondary` responsive con feedback de carga.
- **Verificación**: `php artisan test` → 252/252 tests pasando. Build Vite exitoso (3.88s).

---

# PARTE 2 — PREFERENCIAS DE USUARIO (preferencias.md)

# Preferencias de comunicación y trabajo

- SIEMPRE responder en español y en modo `/caveman ultra` (extrema compresión, bare fragments, listas directas).
- Usar las skills necesarias y relevantes para el proyecto cuando la tarea lo requiera.
- Tener en cuenta el contexto del proyecto AgroFundo (Laravel 13 + Livewire 3 + MySQL) al trabajar.

## Regla de Base de Datos Estricta
- **CERO Seeders para runtime o datos en caliente**: Toda la información se crea, persiste, actualiza y consulta de forma real en la base de datos MySQL (`fundo_parque01`). Nada de datos mockeados ni reseteos con seeders arbitrarios.

## Regla Estricta de Reutilización y Unificación UI/UX
- **Reutilización obligatoria de componentes y funciones estándar**:
  - **Filtros**: usar SIEMPRE `<x-collapsible-filters>` con cuadrícula responsive consistente (filas de 4 a 6 columnas proporcionadas `items-end`, cada input/select con etiqueta individual en mayúsculas `[10px]`, sin subcolumnas deformadas ni inputs encimados).
  - **Selects y Fechas**: usar SIEMPRE `<x-filter-select>` (`tone="emerald"`, `compact` para filtros de tabla) y `<x-date-picker>`.
  - **Tablas**: usar SIEMPRE clases semánticas `.agro-record-surface`, `.agro-record-header`, `.agro-record-list`, y `<x-table-sort-header>`.
  - **Estados vacíos**: usar SIEMPRE `<x-empty-state>`.
  - **Botones y Acciones**: usar `.agro-button`, `.agro-button-secondary`, y `.agro-icon-button`.
  - **Traits**: reusar `HasCsvExport` (`streamCsv`), `HasSorting`, `HasRecentRecord`, `HandlesRecordPhotos`.
  - Cero estructuras ad-hoc o estilos dispersos; máxima reutilización, rendimiento y coherencia visual en todos los módulos.

## Preferencias de diseño UI y Formularios (IMPORTANTE)
- **CERO Degradados Plásticos**: Prohibidos degradados agresivos (`bg-gradient-to-br from-white via-emerald-50...`) en fondos de tarjetas y texto degradado (`bg-clip-text bg-gradient-to-r...`).
- **Superficies Planas SaaS Dual-Mode**:
  - Superficies limpias (`bg-white` / `dark:bg-zinc-900`) con bordes sutiles (`border-zinc-200/90` / `dark:border-zinc-800`).
  - Línea superior de acento temático por sección: `border-t-4 border-t-emerald-500`, `border-t-teal-500`, `border-t-sky-500`, `border-t-amber-500`, `border-t-rose-500`, `border-t-cyan-500`.
- **Alineación Vertical Milimétrica (Baseline Lock)**:
  - Todos los contenedores de inputs (`<input>`, `<x-date-picker>`, `<x-filter-select>`) estandarizados a altura fija `h-11`.
  - Todas las etiquetas en cuadrícula multi-columna fijadas a `mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300` para garantizar que todos los inputs comiencen exactamente en la misma coordenada Y.
- **Distribución de Columnas Responsive (7 / 5 Layout)**:
  - En pantallas desktop (`lg:` / `xl:`): Columna principal de datos `space-y-6 lg:col-span-7` + Columna lateral de fotos/resumen `space-y-6 lg:col-span-5`.
  - Proporción de fotos estandarizada a `aspect-[16/9]` (o cuadrada según contexto) sin deformaciones.
- **Barra de Acciones Inferior Estandarizada**:
  - Tarjeta unificada `rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900`.
  - Botón cancelar: `.agro-button-secondary`.
  - Botón guardar/crear: `.agro-button` con feedback de carga `wire:loading`.
- **Reactividad Alpine/Livewire sin Latencia**:
  - Selector con sincronización inmediata `wire:model.live` o entangle `.live` para actualización instantánea de resúmenes calculados.
- **Finanzas y Botiquín**:
  - En `/finanzas/movimiento/nuevo`: el egreso de Insumos y Materiales (y Medicamentos) carga directamente el formulario de registro desde cero con todos sus campos (`Nombre`, `Categoría/Tipo`, `Unidad`, `Marca`, `Presentación`, `Cantidad`, `Lote INS26-XXX`, `Vencimiento`, `Proveedor`, `Comprobante`, `Ubicación`, `Conservación`, `Alerta stock`). No recupera ni edita datos de registros previos.
  - En `/finanzas/movimiento/editar/{id}`: mantiene el producto vinculado bloqueado para solo editar la compra y el lote.
  - Formato numérico limpio (`formatNumber()`): cantidades enteras fijas sin `.000` (`12`, `2`, `10`) y con decimales reales sólo si aplica.
  - Paleta Emerald exclusiva para Insumos y Materiales con icono de caja de suministros.

---

# PARTE 3 — SEGURIDAD DE DEPENDENCIAS (seguridad-dependencias.md)

# Preferencias de usuario

## Seguridad de dependencias (IMPORTANTE)
- NO instalar dependencias nuevas (npm/composer) sin consultar — riesgo supply-chain (ej. incidente Alpine.js 2024).
- Preferir código propio sobre paquetes nuevos.
- Si un paquete es estrictamente necesario, explicar riesgo y pedir aprobación antes.
- Mantener dependencias existentes; no agregar sin necesidad.

## Base de Datos 100% MySQL (CRÍTICO)
- SQLite ELIMINADO por completo de la aplicación y de la suite de pruebas.
- App local usa MySQL (`DB_DATABASE=fundo_parque01`).
- Tests automatizados usan MySQL con BD dedicada y aislada (`fundo_parque_testing`). La BD de desarrollo nunca se toca ni se borra.
- Email verification: desactivado (sin `MustVerifyEmail` ni middleware `verified`), usuarios auto-verificados con `email_verified_at = now()`.

---

## SESIÓN 2026-08-20/21 — MÓDULO ENGORDE: FIXES Y MEJORAS UI

### Bug fix: LazyLoadingViolationException en /engorde
- **Causa**: Livewire rehidrata `$selectedLote` (prop pública `?LoteEngorde`) sin eager relations. Blade accede `$ea->animal->arete/nombre` dentro de `@foreach` del modal venta.
- **Fix**: `render()` de `Index` llama `$this->selectedLote->loadMissing('animales.animal')` si `$showVenderLoteModal && $selectedLote instanceof LoteEngorde`.
- **REGLA**: props públicas Eloquent en Livewire pierden relaciones al rehidratarse → siempre `loadMissing()` en `render()`.

### Fix comprobantes de venta (404 en Finanzas)
- **Causa**: `Engorde\Index` y `Engorde\Show` guardaban comprobantes en disco `'public'` (`comprobantes/{fundo_id}`), pero `SecureFileController` y `MovimientoShow` leen exclusivamente del disco `'local'` (almacenamiento privado). Resultado: 404 y vista rota en Finanzas (Index, Show, Edit).
- **Fix**: Guardar siempre con `ImageOptimizer::store($file, 'comprobantes', 'comprobanteVenta', 1400, 900*1024, 'local')` o `$file->store('comprobantes', 'local')` (para PDFs).
- **Migración/reparación**: Archivos previos en `storage/app/public/comprobantes` copiados a `storage/app/local/comprobantes`.

### Venta de animales con precio individual + distribución
- **Precios individuales**: Cada animal seleccionado tiene su input `preciosAnimales[$animalId]`. Al escribir se auto-suma al `montoVenta` global.
- **Distribución rápida**:
  - `⚡ Promedio`: Divide `montoVenta` equitativamente entre los seleccionados.
  - `⚖️ Por peso`: Distribuye `montoVenta` proporcionalmente según el peso actual en kg de cada animal.
- **KPIs calculados en vivo**: Muestra Animales seleccionados, Peso Total (kg), Promedio por Animal (S/.) y Precio por kg en pie (S/./kg).
- **Desglose en Finanzas e Inventario**: `Movimiento` guarda el desglose en `descripcion` (`[VENTA LOTE ENGORDE: LOT26-001] BOV26-003: S/ 2,500.00 | BOV26-004: S/ 2,500.00`) y `AnimalInventoryService::linkSale` guarda el precio individual en `detalle_baja` de cada animal.
- **Uploader "Modo Queso"**: Previsualización inmediata, botones Tomar foto / Galería o PDF, descartar archivo, optimización WebP automática, indicador de subida.

### Swal confirm Cerrar / Reabrir lote
- **Cerrar**: modal → valida → `solicitarCierreLote()` → `swal:confirm` → listener `confirmarCierreLote` → `finalizarLoteConfirmado()`.
- **Reabrir**: botón tabla → `solicitarReabrirLote()` → `swal:confirm` → listener `confirmarReabrirLote` → `reabrirLoteConfirmado()`.
- `$listeners`: `confirmarCierreLote→finalizarLoteConfirmado`, `confirmarReabrirLote→reabrirLoteConfirmado`.
- `finalizarLote()` y `reabrirLote()` son wrappers deprecados para retrocompat.

### Fix global: x-date-picker no cerraba al elegir fecha
- **Causa**: `pick()` llamaba `$refs.trigger.focus()` → `@focus` re-abría menú.
- **Fix**: `pick()` solo marca `this.value = iso`, NO cierra ni hace focus.
- **Flujo correcto**: elegir día (queda marcado) → clic **OK** (pill verde, header derecha de `»`) → `closeMenu()` → cierra.
- **Botón OK**: `h-6 px-2 rounded-md text-[10px]`, gris/deshabilitado sin fecha. Global para todos los `x-date-picker`.
- `commitDraft(true)` y Escape hacen `blur()` en vez de `focus()`.

### Estandarización visual de filtros, date-pickers, badges y botones
- **Altura y proporciones unificadas (`h-8` = 32px en barras y cabeceras)**:
  - `x-date-picker` en modo `compact`: `h-8 py-1 pl-3 pr-8 text-xs rounded-lg`.
  - `x-filter-select` en modo `compact`: `h-8 px-3 py-1 text-xs rounded-lg`.
  - Inputs de búsqueda y numéricos de barra de filtros: `h-8 text-xs rounded-lg`.
  - Badges informativos en cabeceras de tarjetas: `inline-flex h-8 items-center rounded-lg border px-3 text-xs font-semibold`.
  - Botones secundarios/toggle inline: `inline-flex h-8 items-center gap-1.5 rounded-lg border px-3.5 text-xs font-semibold`.
  - **Regla de oro**: Cuando convivan badges/píldoras y botones de acción en una misma fila, DEBEN compartir altura (`h-8`), redondeo (`rounded-lg`), tipografía (`text-xs font-semibold`) y grosor de borde (`border`), evitando botones gigantes al lado de etiquetas delgadas.
- **Modales y formularios principales**: `x-date-picker` e inputs de formulario usan `h-10 text-sm rounded-xl`.
### Diagnóstico y pruebas autónomas en tiempo real (Navegador + Test Suite)
- **Flujos interactivos validados en el navegador**:
  - Registro de animal (`/animal/nuevo`): creado bovino `BOV26-001` (ESTRELLA, Holstein Friesian).
  - Filtros en tiempo real (`/animal`): búsqueda debounce, filtrado por especie/raza/género/estado, selector de rango `Desde`/`Hasta` con confirmación por botón `OK`.
  - Producción de leche (`/leche/nuevo`): registrado lote de 120.50 L, comprobado filtrado por turno (Mañana/Tarde/Noche) y renderizado de KPIs.
  - Finanzas (`/finanzas/movimiento/nuevo`): registrado egreso de alimentos `S/ 450.00`, comprobada sincronización y navegación directa.
  - Buscador global (`/buscador`): consultas instantáneas por arete (`BOV26-001`) y concepto (`concentrado`), con enlace y conteo por módulo.
  - Auditoría (`/auditoria`): verificación de logs registrados por cada acción en vivo.
- **Pruebas integrales del módulo de Engorde (`/engorde`)**:
  - Creados 3 toretes de engorde (`BOV26-002` Angus, `BOV26-003` Brahman, `BOV26-004` Simmental) con pesos iniciales reales.
  - Creación de lote (`LOT26-001` LOTE TORILLOS SANTA ROSA) e incorporación selectiva de animales por especie.
  - Registro de pesajes de control: Torillo Alfa (320.00 -> 355.00 kg, +35 kg) y Torillo Bravo (345.00 -> 380.00 kg, +35 kg).
  - Retiro de animal con confirmación SweetAlert (`Torillo Campeon`).
  - Modal de Venta/Liquidación:
    - Distribución rápida `⚡ Promedio` (S/ 3,250.00 c/u) y `⚖️ Por peso` (S/ 3,139.46 y S/ 3,360.54).
    - Ingreso manual de precios individuales (S/ 3,200.00 y S/ 3,600.00) con cálculo automático de suma total (S/ 6,800.00), promedio/animal (S/ 3,400.00) y precio/kg en pie (S/ 9.25/kg).
    - Sincronización automática: creación de ingreso comercial en Finanzas (`+ S/ 6,800.00`) y actualización de estado de animales a `Dado de baja (Venta)`.
    - Transición de lote a `Cerrado` y reapertura instantánea a `Activo`.
    - Generación y descarga de reportes PDF (`Resumen PDF` y `Reporte detallado`).

### Fix Validación de Fechas en Engorde (2026-08-21)
- **Problema**: Al agregar un animal a un lote con fecha de ingreso histórica (ej. 20/08/2026 en lote iniciado el 19/08/2026), el sistema bloqueaba con *"La fecha de ingreso no puede ser anterior al alta del animal (21/08/2026)"* si el animal había sido registrado en el software hoy (21/08/2026).
- **Causa**: `Engorde\Show` validaba `$fechaParsed->isBefore($animal->fecha_alta)`. Sin embargo, `fecha_alta` es la fecha de alta administrativa en el software, no la fecha física de nacimiento ni de existencia del animal.
- **Solución**:
  1. La validación estricta solo aplica contra `fecha_nacimiento` (si está definida): *"La fecha de ingreso no puede ser anterior al nacimiento del animal"*.
  2. Si `fecha_alta` del animal era posterior a la fecha de ingreso al lote, el sistema ahora sincroniza automáticamente la `fecha_alta` del animal a la fecha de ingreso (`$animal->update(['fecha_alta' => $fechaParsed->toDateString()])`), manteniendo coherencia histórica sin bloquear al usuario.
  3. Aplicado tanto en incorporación masiva (`agregarAnimales`) como en edición individual (`actualizarIngreso`).

### Restauración de Backup Real Auténtico & Cero Seeders (2026-08-21)
- **Acción ejecutada**: Restaurado el dump real auténtico `backup_limpio_post_mojibake_20260808.sql` en `fundo_parque01`, aplicado `php artisan migrate --force` y restaurados los registros sanitarios activos.
- **Datos reales activos en MySQL**:
  - Usuarios: `admin` (FRANKLIN CHOQUENAIRA QUISPE) y `pepe` (PEPE).
  - Fundo: `FUNDO CCOLQQUE PARQUE TEXAS`.
  - Ganado real: 9 animales.
  - Sanidad: 11 registros sanitarios clínicos/preventivos activos.
  - Engorde: 3 lotes.
  - Finanzas: 3 movimientos.
  - Ordeños: 2 registros.
  - Botiquín: Catálogo completo con lotes y consumos.
### Estandarización de Ordenamiento LIFO / Recientes y Cabeceras Interactivas (2026-08-21)
- **Directiva**: Todas las tablas del sistema muestran por defecto el **último registro ingresado primero** (LIFO / `id DESC` / `fecha DESC`) y disponen de cabeceras/selectores interactivos para alternar orden alfabético (A-Z, Z-A), fecha, stock y peso.
- **Módulos estandarizados**:
  1. `Animal\Index`: Default `id DESC`. Cabeceras interactivas en `Código`, `Nombre`, `Edad` y `Estado/Registro`.
  2. `Medicamentos\Index`: Default `reciente` (`id DESC`). Cabeceras interactivas en `Medicamento`, `Stock` y `Vencimiento` + selector `A-Z`, `Z-A`, `Mayor Stock`, `Menor Stock`, `Próximo a Vencer`.
  3. `Insumos\Index`: Default `reciente` (`id DESC`). Cabeceras interactivas en `Insumo`, `Stock` y `Vencimiento` + selector alfabético y stock.
  4. `Engorde\Index`: Default `fecha_inicio DESC, id DESC`. Cabeceras interactivas en `Código`, `Nombre`, `Fecha Inicio` y `Estado`.
  5. `Queso\Index`: Default `id DESC`. Cabeceras interactivas en `Fecha`, `Moldes Elaborados` y `Peso Total (Kg)`.
  6. `Finanzas\Index`: Default `fecha DESC, id DESC`. Cabeceras interactivas en `Fecha`, `Categoría/Tipo` y `Monto`.
  7. `Monitoreo\Index`: Sanidad y Partos en `fecha DESC, id DESC` (LIFO). Alertas en `fecha_alerta ASC` (próximas a vencer).
- **Resultados de verificación**:
  - `ComprehensiveWorkflowTest.php`: 5/5 passed (31 assertions, 0 errors).
  - Auditoría HTTP: 28/28 rutas con HTTP 200 OK.
  - Assets: `npm run build` compilado en 5.99s.

### Personalización Global de Reportes PDF, Marca de Agua y Bloque de Firmas (2026-08-21)
- **Directiva**: Implementar un panel completo y elegante en `/ajustes` (pestaña `Reportes y PDF`) para configurar en tiempo real la apariencia, marca de agua dinámica, bloque de firmas con sello de autenticidad, cabecera con logo y pie de página en todos los 10 reportes PDF del sistema.
- **Servicio y Soporte**:
  - `App\Support\PdfReportConfig`: Servicio singleton centralizado para recuperar y persistir la configuración en `configuracion_sistema` por `fundo_id` con caché inteligente. Inyectado globalmente en todas las vistas mediante `AppServiceProvider` (`$pdfConfig`).
  - Paletas de color: Esmeralda (`#047857`), Azul Marino (`#0369a1`), Ámbar Cálido (`#b45309`), Pizarra (`#334155`), Púrpura Real (`#7c3aed`) y Personalizado (Hex).
  - Presets de opacidad de marca de agua: `2%`, `4% (Recomendado)`, `7%`, `10%`, `15%`.
- **Livewire e Interfaz (`/ajustes?tab=pdf`)**:
  - Trait `App\Livewire\Ajustes\Traits\HasPdfReportSettings`: Carga reactiva, validación en tiempo real, persistencia, reseteo a valores recomendados y descarga de PDF de muestra (`downloadSamplePdf()`).
  - Vista `resources/views/livewire/ajustes/pdf.blade.php`: Configuración interactiva dividida en 4 tarjetas (Marca de agua, Estilo visual y logo, Pie de página y Bloque de firmas) con **hoja de papel simulada en tiempo real (Live Preview)** que refleja cada cambio al instante.
- **Partials Modulares para Dompdf (`resources/views/pdf/partials/`)**:
  1. `styles.blade.php`: CSS unificado para marcas de agua diagonales de alta fidelidad, firmas equilibradas y pie fijo sin solapamiento de márgenes.
  2. `watermark.blade.php`: Renderizado condicional de la marca de agua con rotación `-24deg`, texto personalizado o nombre del fundo, opacidad y color configurables.
  3. `signatures.blade.php`: Soporte para 3 modelos + Sello de Validación Externa:
     - 🛡️ **Sello Digital Certificado (`digital`)**: Cuadros compactos con isotipo del sistema/fundo, titular, cargo, DNI/RUC, SENASA/CMVP, software ganadero oficial, motivo de certificación, fecha/hora con segundos (`d/m/Y H:i:s`) y código HASH SHA-256 de verificación.
     - 🏛️ **Sello de Validación Externa / Bancaria (`mostrar_sello_externo`)**: Sello complementario configurable para entidades financieras (Agrobanco, BCP, BBVA, etc.), analistas de crédito o auditores técnicos, con campos para Entidad, Cargo, Evaluador, Matrícula/Reg., N° Expediente/Operación y Estado (`CONFORME / APROBADO`).
     - ✍️ **Líneas Clásicas (`clasica`)**: Firmas manuscritas con sello de autenticidad.
     - ⚖️ **Doble Certificación (`ambas`)**: Líneas laterales + Sello digital de software central.
  4. `footer.blade.php`: Pie de página profesional con texto institucional, fecha/hora oficial de Perú (`America/Lima`), usuario emisor y contador de páginas Dompdf (`counter(page)`).
- **Plantillas PDF actualizadas (10/10)**:
  `pdf/animales.blade.php`, `pdf/animal.blade.php`, `pdf/medicamentos.blade.php`, `pdf/ordenos.blade.php`, `pdf/queso.blade.php`, `pdf/engorde.blade.php`, `pdf/engorde-detallado.blade.php`, `pdf/monitoreo.blade.php`, `pdf/finance-index.blade.php`, `pdf/finance-record.blade.php`.
- **Vista Previa Horizontal A4 (Landscape)**:
  - Hoja simulada interactiva con proporción `1.414/1` (A4 horizontal apaisado), cabecera, tabla ancha de datos, 2 o 3 sellos digitales horizontales en vivo y foliación real.
  - Toggles tipo switch modernizados con soporte de contraste dark/light mode.
- **Verificación**:
  - Compilación y renderizado DOMPDF: 10/10 vistas PDF renderizadas sin errores de memoria ni dependencias.
  - Tests automatizados: `ComprehensiveWorkflowTest` 5/5 PASSED (31 assertions).
  - Auditoría HTTP: 28/28 rutas con HTTP 200 OK.
  - Assets: `npm run build` compilado en 4.59s.
- **Estandarización de badges y botón de vista previa (`/ajustes?tab=pdf`)**:
  - Unificados `Marca de agua`, `Firmas activas` y el botón `Mostrar/Ocultar vista previa` con la misma altura (`h-8`), bordes redondeados (`rounded-lg`), tipografía (`text-xs font-semibold`) y grosor de línea (`border`), eliminando desproporciones visuales entre píldoras y botón.

### Optimización y Modularización de Firmas, Marca de Agua y Preview Plegable (2026-08-22)
- **Firmas Individuales Separadas**:
  - `mostrar_firma_1` y `mostrar_firma_2` configurables de forma independiente mediante sub-tarjetas con switch track/thumb propio (`peer-checked:translate-x-5`), además del switch maestro `mostrar_firmas`.
  - Plantilla Dompdf (`pdf/partials/signatures.blade.php`) y vista previa en vivo (`livewire/ajustes/pdf.blade.php`) ajustan dinámicamente el layout de columnas (1, 2 o 3 columnas) según las firmas activas.
- **Orientación de Marca de Agua (Diagonal / Recta)**:
  - Selector `orientacion_marca_agua` con opciones `diagonal` (-24° de seguridad anticopia) y `horizontal` / `recto` (0° lectura directa centrada).
  - Soporte CSS en `pdf/partials/styles.blade.php` con rotación dinámica `transform: rotate(...)` y ajuste de `top` (`38%` diagonal, `44%` horizontal).
  - Corrección en vista previa en vivo: uso explícito de `display: inline-block; transform: rotate(...)` en el `<span>` para garantizar rotación instantánea en todos los navegadores.
- **Vista Previa Plegable por Defecto (Estilo Filtros)**:
  - Panel A4 apaisado integrado con `x-data="{ showPreview: false }"` (oculto por defecto para carga ultrarrápida y foco en el formulario).
  - Botón desplegable con chevron animado (`rotate-180`), texto reactivo y badges informativos de firmas y orientación.
- **Estandarización Visual y Proporciones**:
  - Encabezado y tarjetas migrados a `.agro-card` con bordes finos 1px y padding homogéneo (`p-5 sm:p-6`).
  - Eliminación de bordes gruesos (`border-2`) en selectores de modelos, orientación y colores, adoptando `ring-1 ring-emerald-600/50` / dark.
  - Switches estandarizados (`h-6 w-11` con thumb `h-4 w-4`) e inputs a 38px de altura estándar.
- **Verificación de Calidad y Tests**:
  - `tests/Feature/AjustesModuleTest.php`: 14/14 tests pasando (94 assertions) con cobertura completa para firmas individuales y rotación de marca de agua.
  - Suite completa del sistema: **270/270 tests PASSED** (1,808 assertions, 0 fallos).
  - Assets compilados con Vite (`npm run build`).

### Formato Legal de Firmas Digitales y Estandarización Global de Reportes PDF (2026-08-22)
- **Diseño Legal Formal de Firmas Digitales**:
  - Eliminados recuadros, fondos grises y logotipos pesados en el bloque de firmas digitales (formato formal de certificación tipo FirmaPerú).
  - Estructura lineal y compacta (`line-height: 1.15`) con campos: `Firmado digitalmente por:`, `Cargo:`, `DNI / RUC:`, `Motivo:`, `Fecha y hora:` (`d/m/Y H:i:s` Hora oficial de Perú) y `Validación:`.
  - Encabezados con color de acento dinámico y nombres de titulares destacados en negrita mayúscula.
  - Campo `Matrícula / Registro N°` reemplazado por `DNI del evaluador` en formularios y sellos externos.
- **Alineación Dinámica a Extremos y Centro**:
  - **2 Firmas**: Firmante 1 al extremo izquierdo exacto (`text-left`, `w-[48%]`) y Firmante 2 / Externo al extremo derecho exacto (`justify-end`, `text-left` inline).
  - **3 Firmas**: Firmante 1 a la izquierda, Firmante 2 en el centro exacto y Firma 3 a la derecha.
  - **1 Firma**: Contenedor centrado en la página.
- **Estandarización Global de Plantillas PDF (10/10)**:
  - `resources/views/pdf/partials/styles.blade.php`: Inyección unificada de paleta cromática (`accentColor`, `accentDark`, `accentSoft`, `accentBorder`), marcas de agua (diagonal/horizontal), cabeceras, tablas de metadatos con DNI y folios de pie de página para todos los reportes:
    1. `animales.blade.php` (Inventario Animal)
    2. `animal.blade.php` (Ficha Integral del Animal)
    3. `ordenos.blade.php` (Producción de Leche)
    4. `queso.blade.php` (Producción y Transformación Láctea)
    5. `engorde.blade.php` (Lotes de Engorde)
    6. `engorde-detallado.blade.php` (Detalle de Engorde y Pesajes)
    7. `medicamentos.blade.php` (Botiquín e Insumos)
    8. `monitoreo.blade.php` (Monitoreo Sanitario y Reproductivo)
    9. `finance-index.blade.php` (Finanzas y Movimientos)
    10. `finance-record.blade.php` (Comprobante Financiero Individual)
- **Suite de Pruebas Automatizadas**:
  - **270/270 tests pasando** (1,808 assertions, 0 errores).

### Separación Vertical y Proporción Compacta de Firmas (2026-08-23)
- **Separación Vertical Superior**:
  - `signatures.blade.php`: Incrementado margen superior de `3pt` a `14pt` para despegar holgadamente el bloque de firmas de las tablas de datos y contenido superior.
  - `livewire/ajustes/pdf.blade.php`: Ajustado espaciado en la vista previa interactiva A4 a `mt-auto pt-4`.
- **Dimensionado y Tipografía Compacta**:
  - Dompdf: Reducido tamaño de texto a `4.6pt` (cuerpo), `4.8pt` (encabezado) y `5.6pt` (titular) con ancho máximo acotado (`max-width: 185pt` para 2 firmas, `155pt` para 3 firmas).
  - Live Preview: Tipografía compactada a `text-[6.2px]`, `text-[5.8px]` y `text-[7.2px]` con `max-w-[190px]`.
- **Verificación**:
  - `AjustesModuleTest`: 14/14 tests pasando (94 assertions, 0 fallos).
  - Assets compilados con Vite.

### Corrección de Glifos en Firmas y Foliación "Pág. 1 de 1" (2026-08-23)
- **Eliminación de Signos de Interrogación (`?`) en Firmas**:
  - Reemplazadas entidades HTML (`&#10003;`) por etiquetas formales en texto plano (`Validación: [Software]`, `Validación Técnica: Conforme`, `Dictamen: [Estado]`).
  - Corrige la incompatibilidad de codificación de caracteres en fuentes Dompdf que generaba el símbolo `?`.
- **Foliación y Pie de Página Ultra-Preciso**:
  - `footer.blade.php`: Reemplazado término `Página` por el formato compacto y técnico `Pág.` (`Pág. 1`, `Pág. 2`, etc.).
  - `livewire/ajustes/pdf.blade.php`: Vista previa en vivo actualizada a `Pág. 1 de 1` con formato horizontal limpio.
  - Eliminados textos redundantes en pie de página para maximizar concisión y elegancia.

### Sistema Cromático Avanzado, Esquinas Curvadas y Escalamiento Proporcional de Firmas (2026-08-23)
- **Paleta Cromática de 16 Presets Ricos y Suaves**:
  - Implementados en `PdfReportConfig::COLOR_PRESETS`: `emerald`, `sky`, `indigo`, `amber`, `rose`, `slate`, `teal`, `violet`, `cyan`, `lime`, `orange`, `fuchsia`, `stone`, `purple`, `olive`, `coffee`.
  - Cada preset define `primary`, `dark`, `soft`, `border`, `ring` y `row_even` (filas alternadas de cebra).
  - Modo Multicolor Armónico para reportes con múltiples tablas (ej. Engorde por lotes, control lechero, finanzas) con rotación secuencial configurable mediante checkboxes de selección múltiple.
- **Bordes y Curvatura de Tablas (Esquinas Suaves vs Rectas)**:
  - Soporte para radio de esquinas continuo `radio_esquinas` con opciones enteras: `0 (Recto)`, `2 pt`, `3 pt`, `4 pt`, `5 pt (Estándar)`, `6 pt`, `8 pt`, `10 pt (Redondeado)`.
  - Botones de selección única con badge e indicador de check integrado.
  - Implementación CSS con `border-radius`, `overflow: hidden`, bordes sólidos sutiles y cabeceras de lote con tag de número y badge coloreado.
- **Escalamiento y Proporción Dinámica de Firmas Digitales (40% - 140%)**:
  - Botones de escala rápida con checks numéricos: `[40% Mín, 50% Ext, 60% Ult, 70% Comp, 80% S-Lig, 90% Lig, 100% Est, 110% Med, 120% Dest, 130% Gran, 140% Máx]`.
  - Slider manual de ajuste fino de `35%` a `150%` (`step="5"`).
  - Soporte backend en `PdfReportConfig` y `HasPdfReportSettings` con métodos `signatureScale()`, `signatureBodyFontSizePt()`, `signatureTitleFontSizePt()`, `signatureLabelFontSizePt()` y `signatureMaxWidthPt()`.
- **Ajuste y Compactación Vertical de Firmas**:
  - Eliminación de separación vertical excesiva entre el nombre del titular en negrita y las líneas de metadatos (`Cargo:`, `DNI:`, etc.).
  - `line-height` ajustado a `1.04/1.05` y márgenes reducidos a `0.2pt`/`1.5px` en Dompdf, vista previa simulada A4 y bloque interactivo de 3 firmas paralelas.
- **Estructura Modular Limpia en Ajustes PDF**:
  - **Tarjeta 1**: `1. Firmas oficiales (individuales)`
  - **Tarjeta 2**: `2. Sello bancario / evaluación externa`
  - **Tarjeta 3**: `3. Escala y proporción de firmas digitales` (Escalas 40%-140% + vista interactiva de 3 firmas en paralelo)
  - **Tarjeta 4**: `4. Esquinas y bordes de tablas` (Curvadas/Rectas + selector numérico 0..10)
  - **Tarjeta 5**: `5. Color institucional y rotación multicolor` (16 presets + paleta rotativa + vista interactiva de 3 tablas de lote en paralelo)
  - **Tarjeta 6**: `6. Marca de agua & pie legal` (Diagonal/Recto, opacidad, color, foliación)
- **Suite de Pruebas Automatizadas**:
  - `tests/Feature/PdfDigitalSignatureAndVerificationTest.php`: 5/5 PASSED (66 assertions).
  - `tests/Feature/PdfDynamicPaginationAndHarmonizationTest.php`: 13/13 PASSED (62 assertions).
  - 100% de aserciones validadas con 0 errores.

### Corrección de Líneas Salientes y Armonización en Tablas PDF (2026-08-31)
- **Eliminación de Líneas y Puntas Salientes en Tablas Dompdf**:
  - **Causa raíz**: El uso de `border-collapse: separate` en combinación con `border-radius` en celdas perimetrales generaba artefactos de trazo en Dompdf:
    - Las líneas de borde vertical exteriores de la tabla se dibujaban rectas sobresaliendo por encima de las esquinas redondeadas del encabezado `th` (imágenes 2 y 5).
    - Los bordes verticales de celda (`td`) sobresalían en intersecciones en "T" por diferencias de redondeo sub-pixel al final de las filas (imágenes 1, 3 y 4).
  - **Solución definitiva aplicada**:
    - Se estandarizó el modelo `border-collapse: collapse !important; border-spacing: 0 !important;` en todas las tablas de datos, resúmenes y metadatos (`.data-table`, `table.data`, `table.data-alt`, `.summary-table`, `.meta-table`, etc.).
    - Se asignaron bordes homogéneos colapsados (`border: 0.8px solid`) entre celdas `th` y `td`, eliminando 100% de los sobre-trazos, espigas y desbordes en cualquier nivel de escala o página.
    - Los radios de curvatura (`border-radius: {{ $radius }}`) se aplican limpiamente en contenedores estructurados (`.summary-card`, `.summary-wrap`, `.lot-title-bar`, `.section-title`, `.meta-card`, `.badge`, `.status`, `.signature-card`, `.photo-box`), preservando la armonía visual sin distorsionar las tablas.
    - Se rediseñó la barra superior del modal de vista previa PDF (`pdf-preview-modal.blade.php`) a **1 sola línea ultracompacta**:
      - Elementos externos fijos: `[← Opciones]`, `[⚙ Ajustes]`, `[📄 Título + Badge]`, `[🎨 Formato ▾]` (Menú desplegable), `[🛡 Firmar Digital]`, `[📥 Descargar]`, `[✕ Cerrar]`.
      - Menú flotante desplegable (`Formato`): contiene la escala de tabla (45%..100%), coloración (Multi / Mono + 8 tonos) y firmas (ON/OFF + escala 45%..155%). Oculto de forma predeterminada para máxima visibilidad del visor PDF.
      - **Ajuste Responsive Móvil**: Solucionado solapamiento de botones en pantallas estrechas con abreviaciones inteligentes (`xs:`, `sm:`, `md:`, `lg:`) y menú flotante fijado y centrado (`fixed inset-x-3 top-14 sm:absolute sm:inset-auto sm:right-0`), eliminando recortes y desbordes.
      - **Corrección de Scroll Vertical**: Añadido `min-h-0` en contenedores Flexbox (`relative flex-1 min-h-0 overflow-hidden`), `overscroll-behavior-y: contain`, `touch-action: pan-y` y `-webkit-overflow-scrolling: touch` para navegación vertical fluida sin bloqueos en móviles y escritorio.
- **Verificación**:
  - Suite completa: **288/288 tests PASSED** (1,935 assertions).
  - Renderizado por lotes de las 10 vistas PDF validado con éxito.



