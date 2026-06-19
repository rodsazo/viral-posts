# Preparación para producción — lista de mejoras

> Documento **vivo**: lista priorizada de lo que conviene resolver antes (o poco después) de pasar a producción. Se irá ampliando. Estado de hoy: la app corre en desarrollo con **SQLite** y datos demo; aún no hay despliegue.

Leyenda de prioridad: 🔴 Alta · 🟡 Media · 🟢 Baja/Futuro.

---

## 1. 🟢 Backups automáticos de base de datos *(acordado: a futuro)*

**Qué:** copias de seguridad programadas de la BD (y archivos subidos, si los hubiera), guardadas **fuera del servidor** y con política de retención.

**Por qué:** evitar pérdida de datos. Hoy, en desarrollo, ya tuvimos un borrado por `migrate:fresh`; en producción una pérdida es mucho más grave (datos reales de la marca/cliente).

**Cómo (propuesta):**
- Paquete `spatie/laravel-backup`: dump de la BD + zip, subida a almacenamiento remoto (S3 / Backblaze / similar).
- Programar diario vía `schedule` (`app/Console`/`routes/console.php`) + un cron real (o el scheduler del hosting).
- **Retención** (p. ej. 7 diarios, 4 semanales) y **notificación** si un backup falla.
- En el flujo de **deploy**, hacer un backup *antes* de correr migraciones.
- Mientras tanto, en dev ya existe la salvaguarda manual: copia a `storage/db-backups/` antes de cualquier cambio de esquema.

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
- **No** ejecutar el seeder demo en producción (o separarlo: dejar global/`HerasTemplateSeeder` y crear el admin real con un comando seguro, no con contraseña fija).
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
  - **Node ≥ 22.12** (o ≥ 20.19) para el build de Vite 8/Flux — la 22.0–22.11 falla (`rolldown-binding…node`). Fijado en `.nvmrc` (24.16) y `engines`. Dev en M3 (arm64), prod en **Ubuntu (linux-x64)**: en el deploy correr `npm ci && npm run build` con Node ≥22.12 (npm baja el binario `linux-x64-gnu` automáticamente), **o** construir en CI y enviar `public/build/` (la salida es portable).
- **Colas y correo reales:** driver de cola (`database`/`redis`) y `mail` real (notificaciones, restablecer contraseña).
  - 🔴 **El asistente de IA del Estudio depende de un worker de cola** (`GenerateSuggestionsJob`). En producción hay que
    correr un **worker supervisado** (`php artisan queue:work`, vía Supervisor/systemd o el runner del hosting) y
    monitorizar fallos. En dev: `php artisan queue:work` junto a `php artisan serve`. Ver [IA.md](IA.md).
- **CI:** pipeline que corra `php artisan test` + Pint (+ PHPStan) en cada push.
- **Multi-usuario con roles** (P2): si entra equipo/clientes, permisos finos por marca.
- **Pipeline de deploy** reproducible (zero-downtime, migraciones controladas, rollback).
- **Política de retención/exportación de datos** por marca (export/import JSON).
