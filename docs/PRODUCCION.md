# Preparación para producción — lista de mejoras

> Documento **vivo**: lista priorizada de lo que conviene resolver antes (o poco después) de pasar a producción. Se irá ampliando. Estado de hoy: la app corre en desarrollo con **SQLite** y datos demo; aún no hay despliegue.

Leyenda de prioridad: 🔴 Alta · 🟡 Media · 🟢 Baja/Futuro.

---

## 0. 🚀 Despliegue en Laravel Cloud (plan elegido)

> Servicio elegido: **Laravel Cloud** (PaaS oficial). Deploy por `git push` a `main`, sin SSH/SFTP.
> El repo ya quedó **preparado** para esto (ver "Lo que ya está hecho"). Aquí van los pasos del primer deploy.

### Lo que ya está hecho en el repo
- **Disco de logos configurable** (`config/filesystems.php` → `brand_disk`, variable `BRAND_FILESYSTEM_DISK`).
  En local sigue siendo `public`; en prod se pone `s3` para que los logos **persistan entre deploys** (el disco
  del contenedor es efímero). `Account::logoUrl()` y los dos `FileUpload` del admin ya respetan ese disco.
- **Seeder seguro:** `DatabaseSeeder` solo siembra el **catálogo global** (Heras) y, **solo en `local`**, el
  `DemoSeeder` (usuarios `password` + contenido demo). En producción el seeder demo **no** corre.
- **Admin real por consola:** `php artisan app:create-admin` crea el usuario admin con **contraseña elegida**
  (nunca fija), opcionalmente su primera marca y/o super admin. Ver [USUARIOS.md](USUARIOS.md).
- **Plantilla de entorno:** [`.env.production.example`](../.env.production.example) con todas las variables a
  definir en el panel de Cloud.

### Pasos del primer deploy
1. **Crear el proyecto** en Laravel Cloud y conectar el repo de GitHub; rama de deploy: `main`.
2. **Base de datos:** adjuntar un **PostgreSQL** gestionado (Cloud inyecta `DB_*`). Pon `DB_CONNECTION=pgsql`.
3. **Object storage:** crear un **bucket** (Cloud rellena `AWS_*`) y definir `BRAND_FILESYSTEM_DISK=s3`.
4. **Variables de entorno:** copiar las de `.env.production.example`; añadir los **secretos** a mano:
   `ANTHROPIC_API_KEY`, `FONTAWESOME_URL`, credenciales de `MAIL_*`. `APP_KEY` lo genera Cloud.
5. **Build:** `composer install --no-dev --optimize-autoloader` + `npm ci && npm run build` (Node ≥ 22.12; `.nvmrc`=24.16).
6. **Comandos de deploy (release):** `php artisan migrate --force` · `php artisan config:cache` ·
   `php artisan route:cache` · `php artisan view:cache` · `php artisan filament:upgrade`.
   *(No hace falta `storage:link`: los logos van a S3.)*
7. **Worker de cola:** activar un proceso **`php artisan queue:work`** (la IA del Estudio lo necesita; sin él se
   queda en "Generando…"). Ver [IA.md](IA.md).
8. **Scheduler + backups:** activar el scheduler. Crear un **bucket aparte** para backups y definir
   `BACKUP_DISK=backups`, `BACKUP_AWS_*` y `BACKUP_NOTIFICATION_EMAIL` (ver punto #1). El scheduler corre
   `backup:run` a diario.
9. **Primer arranque:** ejecutar `php artisan app:create-admin` (consola/command runner de Cloud) y entrar a `/admin`.
   **No** ejecutar el seeder demo.

### Migrar los datos reales de dev (SQLite → Postgres)
Los datos actuales viven en SQLite (`database/database.sqlite`). Para llevarlos a Postgres sin `migrate:fresh`:
- Opción simple: en prod correr `php artisan migrate --force` (esquema) y **recrear** lo imprescindible a mano
  (admin con `app:create-admin`; los catálogos Heras los siembra `DatabaseSeeder`).
- Opción "copiar datos": exportar por tabla desde SQLite e importar a Postgres (script de migración de datos),
  cuidando los tipos. Dejar esto como tarea aparte si se necesita conservar el contenido demo/real.

---

## 1. ✅ Backups automáticos off-site *(implementado)*

**Qué:** copias de seguridad **diarias** de la BD, guardadas en un bucket **separado** del almacenamiento
principal (off-site real), con retención y aviso por correo si fallan.

**Por qué:** evitar pérdida de datos. Los snapshots del Postgres gestionado de Cloud cubren el "se cayó el
servidor"; esto añade copias **portables y fuera del proveedor** (restaurables en otro sitio, o si se pierde
acceso a la cuenta).

**Cómo (ya hecho con `spatie/laravel-backup`):**
- **Destino:** disco `backups` (S3-compatible, bucket/proveedor aparte; credenciales `BACKUP_AWS_*`).
  Config en `config/filesystems.php` y `config/backup.php`.
- **Programación:** `routes/console.php` corre `backup:clean` (01:30) y `backup:run` (02:00) a diario.
  Requiere el **scheduler de Cloud** activo.
- **Notificación:** solo si **falla** (a `BACKUP_NOTIFICATION_EMAIL`); los éxitos no spamean.
- **Qué respalda:** el **dump de la BD** (lo crítico). Los **logos** viven en S3 y su durabilidad la da el
  bucket — activa **versioning** en él para tener punto-en-el-tiempo.
- **Probar:** `php artisan backup:run` (o `--only-db`); ver estado con `php artisan backup:list`.
- **Pendiente opcional:** lanzar un backup *antes* de migrar en el deploy; cifrar el zip con `BACKUP_ARCHIVE_PASSWORD`.
- En dev sigue la salvaguarda manual: copia a `storage/db-backups/` antes de cualquier cambio de esquema.

---

## 2. 🔴 Base de datos de producción (PostgreSQL o MySQL)

**Qué:** dejar SQLite solo para desarrollo y usar **PostgreSQL** (recomendado en el handoff) o MySQL en producción.

**Por qué:** SQLite no es ideal con concurrencia (varios usuarios/escrituras simultáneas), ni para backups/replicación/escalado. El esquema actual no usa nada específico de SQLite, así que el cambio es de configuración, no de código.

**Cómo:**
- Provisionar Postgres gestionado (del hosting/proveedor).
- Configurar `DB_CONNECTION=pgsql` y credenciales en `.env` de producción.
- `php artisan migrate` (aditivo) y verificación de humo.
- Validar que los enums (guardados como `string`) y las columnas funcionan igual (lo hacen).

---

## 3. 🔴 Endurecer configuración y credenciales

**Qué:** configuración segura del entorno y **eliminar las credenciales/datos demo** antes de exponer la app.

**Por qué:** seguridad. Hoy el `DatabaseSeeder` crea un usuario `rodsazo@gmail.com` con contraseña **`password`** y datos demo: eso **no puede** llegar a producción.

**Cómo:**
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` propia y secreta, `APP_URL` con **HTTPS**.
- ✅ **Hecho:** el seeder demo ya está **separado** (`DemoSeeder`, solo en `local`) y el admin real se crea con
  `php artisan app:create-admin` (contraseña elegida, no fija). En prod corre solo el catálogo global.
- Cookies de sesión seguras (`SESSION_SECURE_COOKIE=true`), forzar HTTPS.
- **Rate limiting** en el login y **verificación de email**; revisar políticas de contraseña.
- Gestión de secretos fuera del repo (variables del hosting / vault). Incluye la **`ANTHROPIC_API_KEY`**
  del asistente de IA (ver [IA.md](IA.md)): solo por entorno, nunca en el repo; rotar si se expone.
  Considerar además un **límite de uso/coste** de IA por marca (cada generación es una llamada de pago).

---

## 4. 🔴 Observabilidad: logs + monitoreo de errores

**Qué:** visibilidad de fallos en producción **antes** de que los reporte el usuario.

**Por qué:** sin monitoreo, un error en una pantalla pasa desapercibido hasta que alguien se queja.

**Cómo:**
- Integrar **Sentry** (o Laravel Flare): captura de excepciones con contexto.
- Canal de logs adecuado (`LOG_CHANNEL`, nivel `warning`+ en prod) y rotación.
- **Uptime/health check** externo de la URL del panel.

---

## Candidatos adicionales (a desarrollar más adelante)

- **Cachés de despliegue + assets:** `config:cache`, `route:cache`, `view:cache`, cache de componentes Filament, `npm run build`, OPcache.
  - **`php artisan storage:link`** solo si los logos usan el disco `public` (dev). En prod los logos van a **S3**
    (`BRAND_FILESYSTEM_DISK=s3`), así que el symlink **no** es necesario (ver punto #0).
  - **Node ≥ 22.12** (o ≥ 20.19) para el build de Vite 8/Flux — la 22.0–22.11 falla (`rolldown-binding…node`). Fijado en `.nvmrc` (24.16) y `engines`. Dev en M3 (arm64), prod en **Ubuntu (linux-x64)**: en el deploy correr `npm ci && npm run build` con Node ≥22.12 (npm baja el binario `linux-x64-gnu` automáticamente), **o** construir en CI y enviar `public/build/` (la salida es portable).
- **Colas y correo reales:** driver de cola (`database`/`redis`) y `mail` real (notificaciones, restablecer contraseña).
  - 🔴 **El asistente de IA del Estudio depende de un worker de cola** (`GenerateSuggestionsJob`). En producción hay que
    correr un **worker supervisado** (`php artisan queue:work`, vía Supervisor/systemd o el runner del hosting) y
    monitorizar fallos. En dev: `php artisan queue:work` junto a `php artisan serve`. Ver [IA.md](IA.md).
- **CI:** pipeline que corra `php artisan test` + Pint (+ PHPStan) en cada push.
- **Multi-usuario con roles** (P2): si entra equipo/clientes, permisos finos por marca.
- **Pipeline de deploy** reproducible (zero-downtime, migraciones controladas, rollback).
- **Política de retención/exportación de datos** por marca (export/import JSON).
