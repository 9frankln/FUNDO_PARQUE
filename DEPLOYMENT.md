# 🚀 Guía de Despliegue — AgroFundo (Producción)

Documento paso a paso para subir **AgroFundo** (Laravel 13 + Livewire 3 + MySQL) a un servidor web público. Sigue el orden exacto para no romper nada.

---

## 1. Requisitos del servidor

| Requisito | Versión mínima | Notas |
|---|---|---|
| PHP | **8.3+** | Extensiones: `openssl`, `pdo_mysql`, `mbstring`, `zip`, `gd` (o `imagick`), `fileinfo`, `intl` |
| MySQL | **8.0+** | (o MariaDB 10.6+) |
| Composer | 2.x | — |
| Node.js | 20+ | Solo para compilar assets |
| Espacio | 2 GB+ | Según el volumen de fotos |
| HTTPS | — | Recomendado: Let's Encrypt / certificado del hosting |

> 💡 **Hosting compatible:** Hostinger, cPanel, Plesk, AWS, DigitalOcean, VPS, o cualquier hosting con PHP 8.3 + MySQL.

---

## 2. Subir el código al servidor

```bash
# Clonar el repositorio (o subir por FTP/zip a la carpeta pública)
git clone https://github.com/9frankln/FUNDO_PARQUE.git
cd FUNDO_PARQUE

# Instalar dependencias de PHP (sin scripts de dev)
composer install --no-dev --optimize-autoloader --no-interaction
```

---

## 3. Configurar el entorno

```bash
# 1. Crear el .env a partir de la plantilla de producción
cp .env.production .env

# 2. Editar el .env y completar:
#    - APP_URL      → https://tu-dominio.com   (CON https, sin barra final)
#    - APP_KEY      → php artisan key:generate (o mantener la de local)
#    - DB_DATABASE  / DB_USERNAME / DB_PASSWORD → datos de tu BD MySQL
#    - MAIL_*       → SMTP real (Hostinger/Gmail/etc.)
```

> ⚠️ **`BACKUP_ARCHIVE_KEY`**: si ya generaste backups cifrados, deja la misma clave de tu entorno anterior. Si cambia, los backups antiguos no se podrán leer.

---

## 4. Base de datos y migraciones

```bash
# Ejecutar migraciones (crea/actualiza todas las tablas e índices)
php artisan migrate --force

# Datos iniciales (roles, permisos, usuario admin)
php artisan db:seed --force
```

> Las migraciones incluyen **todos los índices de alto rendimiento** ya preparados para el sistema (animales, ordenos, engorde, sanidad, alertas, auditoría, etc.).

---

## 5. Compilar assets y enlazar storage

```bash
# Compilar CSS/JS de producción (debe ejecutarse en la máquina de build o aquí)
npm install --ignore-scripts
npm run build

# Crear el enlace público para las imágenes (FOTOS del sistema)
php artisan storage:link
```

> **Esto es crítico:** sin `storage:link`, todas las fotos de animales, queso y la landing dan **404**. Verifica que `public/storage` apunte a `storage/app/public`.

---

## 6. Optimizaciones de Laravel (obligatorias en producción)

```bash
php artisan config:cache    # Cachea la configuración
php artisan route:cache     # Cachea las rutas
php artisan view:cache      # Compila las vistas
php artisan event:cache     # Cachea los eventos
```

> Después de cualquier cambio en `app/`, `.env` o rutas, vuelve a ejecutar estos comandos (o `php artisan optimize`).

---

## 7. Colas, scheduler y worker

```bash
# 1. Worker de colas (necesario para backups, mails y trabajos futuros)
php artisan queue:work --sleep=3 --tries=3

# 2. Scheduler (backups automáticos + poda de auditoría cada día 03:30)
#    Añade a crontab (Linux):
#    * * * * * cd /ruta/a/FUNDO_PARQUE && php artisan schedule:run >> /dev/null 2>&1
```

> Sin el scheduler no se ejecutarán los **backups automáticos** ni la **poda de auditoría** (retención 180 días).

---

## 8. Permisos de archivos

```bash
# Asegurar permisos correctos (Linux)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public
```

---

## 9. HTTPS y dominio

1. Configura el dominio en tu hosting apuntando a la carpeta `public/`.
2. Activa HTTPS (Let's Encrypt o el certificado de tu hosting).
3. Verifica que `APP_URL` use `https://` — **si queda en `http://127.0.0.1:8000`, las imágenes y enlaces se generan rotos**.

---

## 10. Tuning de MySQL (BD rápida)

Agrega o ajusta en `my.cnf` / `my.ini` (valores orientativos para 2–4 GB RAM):

```ini
[mysqld]
# Tamaño del buffer de caché de índices (crítico para consultas rápidas)
innodb_buffer_pool_size = 1G

# Log de consultas lentas para detectar cuellos de botella
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Optimiza inserciones masivas
innodb_flush_log_at_trx_commit = 2

# Conexiones
max_connections = 150
```

> El sistema ya usa **índices compuestos** y **queries agregadas en BD** (GROUP BY), además de **caché de dashboards** (3–5 min), así que la BD responde rápido incluso con volúmenes grandes.

---

## 11. Checklist de verificación post-despliegue

- [ ] `APP_ENV=production` y `APP_DEBUG=false` en el `.env`
- [ ] `APP_URL` con `https://` y dominio real
- [ ] `public/storage` enlazado y **las fotos se ven** (no 404)
- [ ] `public/hot` **NO existe** en el servidor
- [ ] La landing pública carga con imágenes y estilos
- [ ] `php artisan config:cache`, `route:cache`, `view:cache` ejecutados
- [ ] Scheduler activo en cron (backups + poda)
- [ ] Worker de colas corriendo
- [ ] Logs rotan a diario (`LOG_STACK=daily`)
- [ ] Sesiones en `database` (permite control de sesiones activas del sistema)
- [ ] `.env` **NO está** en el repositorio ni expuesto públicamente

---

## 12. Notas de rendimiento aplicadas

| Área | Optimización |
|---|---|
| **Dashboard** | Agregaciones cacheadas 3 min por fundo+permisos (antes: 15+ queries por carga) |
| **Animal / Engorde** | Altas mensuales y breakdowns con `GROUP BY` en BD + caché 5 min (antes: cargaba 12 meses a memoria en cada render) |
| **Buscador** | Resultados cacheados 60 s + debounce 300 ms + límites por categoría |
| **Sesiones** | Touch diferido: escribe en BD solo cada 60 s (antes: 1 SELECT + 1 UPDATE por request) |
| **Auditoría** | Poda automática: retención 180 días + índice en `created_at` |
| **Frontend** | Fuentes no bloqueantes, lazy loading de imágenes, build minificado |
| **Storage** | Symlink reparado, placeholder de imagen creado, archivos sensibles fuera del repo |

---

## 13. Troubleshooting rápido

| Síntoma | Causa probable | Solución |
|---|---|---|
| Fotos 404 | `storage:link` no ejecutado | `php artisan storage:link` |
| Sin estilos/JS | `public/hot` presente o build viejo | borrar `public/hot` + `npm run build` |
| Enlaces con `127.0.0.1` | `APP_URL` mal configurado | corregir `APP_URL` + `php artisan config:cache` |
| 500 en landing | caché de config con URL vieja | `php artisan config:clear` y re-cachear |
| Backups no se crean | scheduler inactivo | revisar cron del `schedule:run` |
