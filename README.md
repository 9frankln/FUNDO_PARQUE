# AgroFundo

Sistema web multi-fundo para gestionar ganado, engorde, ordeños, producción de queso, finanzas, sanidad, profilaxis, partos y alertas.

## Tecnologías

- PHP 8.3 y Laravel 13
- Livewire 3, Volt y Blade
- MySQL 8 o SQLite
- Tailwind CSS 3 y Vite 8

## Instalación

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan fundo:provision
php artisan storage:link
npm ci
npm run build
```

Configura la conexión de base de datos en `.env` antes de ejecutar las migraciones.

## Desarrollo

```powershell
composer run dev
```

También puedes ejecutar los servicios por separado:

```powershell
php artisan serve
npm run dev
php artisan queue:listen
php artisan schedule:work
```

## Pruebas

```powershell
composer test
```

La suite usa SQLite en memoria y no modifica la base de datos configurada para desarrollo.

## Provisionamiento y demostración

`DatabaseSeeder` crea únicamente roles, permisos y catálogos base. No crea fundos, usuarios ni datos operativos.

`php artisan fundo:provision` solicita el fundo y las credenciales iniciales sin guardar una contraseña fija en el código. Para ejecución no interactiva, define temporalmente `INITIAL_ADMIN_PASSWORD` y pasa `--fundo`, `--name`, `--email` y `--username`.

Datos ficticios, solo en `local` o `testing`:

```powershell
php artisan db:seed --class="Database\Seeders\DemoDataSeeder"
```

## Seguridad

- Todas las rutas de negocio requieren autenticación, correo verificado y fundo activo.
- Los permisos se aplican por módulo y acción.
- Los respaldos ZIP se guardan en almacenamiento privado con AES-256, firma HMAC y checksum SHA-256.
- Cada programación permite elegir datos, fotos/archivos o todo el fundo, además de Gestión web y Auditoría.
- La importación de respaldos admite archivos ZIP de hasta 10 GB; PHP y el servidor web deben conservar ese límite o uno superior.
- Auditoría se archiva como evidencia de solo lectura; credenciales, usuarios y sesiones no se incluyen ni se restauran.
- En producción ejecuta `php artisan schedule:run` cada minuto para activar backups programados.
- En producción usa `APP_ENV=production` y `APP_DEBUG=false`.
