# Convenciones del proyecto

Sistema de gestión de producción de contenido. **Laravel 13 + Filament v5** (panel admin) y un
frontend a medida **Livewire 4 + Flux** (el "Estudio"), multi-marca con roles. Contexto completo en
[docs/HANDOFF.md](docs/HANDOFF.md); pendientes de producción en [docs/PRODUCCION.md](docs/PRODUCCION.md);
asistencia con **IA (Claude)** en [docs/IA.md](docs/IA.md).

## Reglas

- **QA al día.** Tras añadir o modificar cualquier funcionalidad, actualiza también la suite de pruebas
  manuales en [`/qa/`](qa/README.md) (caso por caso). Si es un área nueva, añade `qa/NN-area.md` y enlázala.
- **Nunca `migrate:fresh` / `migrate:refresh`** sobre la BD de desarrollo: contiene datos reales del usuario.
  Usa solo migraciones **aditivas** (`php artisan migrate`) y, antes de migrar, copia
  `database/database.sqlite` a `storage/db-backups/`.
- **Tests y formato.** Antes de dar por hecho un cambio: `php artisan test` en verde y `./vendor/bin/pint`.
- **Assets / Node.** Vite 8 + Flux requieren **Node ≥ 22.12** (`.nvmrc` = 24.16). `npm run build` para el Estudio.
- **Livewire: nombres reservados.** No nombres propiedades/métodos como `hook`, `pull`, `url`, etc. (colisionan
  con `Livewire\Component`). Ya usamos `hookText`, `findCapture`, `postUrl`.
- **Multi-tenancy.** Todo lo de marca lleva `account_id` y se escopa por la marca activa; los catálogos de
  Referencia (Heras/Referentes/Nichos) son **globales** (`$isScopedToTenant = false`).
- **Roles.** Dos niveles: por marca (`TeamRole` Admin/Editor) y de plataforma (`users.is_super_admin`). El
  **super admin** gobierna catálogos globales, marcas y usuarios, y se gestiona **solo por consola**
  (`php artisan super-admin grant|revoke|list`). No hay UI para otorgarlo. Detalles en [docs/USUARIOS.md](docs/USUARIOS.md).
- **IA = sugerencia, no reemplazo.** Toda función de IA ofrece **hasta 3 alternativas**; el usuario elige y
  **solo al elegir** se reescribe su contenido. Modelo `claude-opus-4-8`; clave **solo** en `.env`
  (`ANTHROPIC_API_KEY`), nunca hardcodeada. Detalles en [docs/IA.md](docs/IA.md).
