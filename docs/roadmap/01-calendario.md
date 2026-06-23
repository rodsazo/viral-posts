# Spec — Calendario editorial / cadencia

> Estado: **propuesta** (sin implementar). Prioridad 🔴. Parte de [README.md](README.md), fase 4 del ciclo.

## Qué

Una vista de **calendario** en el Estudio que organiza las piezas por **fecha de publicación prevista**
(no solo por estado, como el Kanban). Permite: ver el plan del mes/semana, arrastrar una pieza del
backlog a un día, detectar **huecos** frente a una cadencia objetivo y planear con antelación.

## Por qué

Tras planificar un backlog, la primera pregunta es *"¿cuándo publico esto?"*. Hoy el Kanban responde
"¿en qué fase está?", pero no "¿qué día sale?". La portada "Inicio" ya intuye *huecos*; el calendario
lo hace concreto y accionable. Es la pieza que falta para pasar de *generar* a *planificar de verdad*.

## Modelo de datos (migraciones **aditivas**)

Reutiliza `ContentPiece`. Hoy ya tiene `published_at` (fecha **real** de publicación). Falta la fecha
**prevista** y la cadencia objetivo de la marca.

1. **`content_pieces.scheduled_for`** — `dateTime`, nullable. Cuándo se *planea* publicar.
   - Distinción clave: `scheduled_for` = plan; `published_at` = hecho. Una pieza "programada" tiene
     `scheduled_for` futuro y `published_at` null; una "publicada" tiene `published_at`.
   - Index en `(account_id, scheduled_for)` para las consultas de rango del calendario.
2. **`accounts.weekly_cadence`** — `unsignedTinyInteger`, nullable. Nº de piezas/semana objetivo de la
   marca. Sirve para calcular huecos ("esta semana 2 de 5 programadas").

```
Schema::table('content_pieces', fn (Blueprint $t) =>
    $t->dateTime('scheduled_for')->nullable()->after('published_at')->index());
Schema::table('accounts', fn (Blueprint $t) =>
    $t->unsignedTinyInteger('weekly_cadence')->nullable()->after('ideal_customer_profile'));
```

> Recordatorio de convención: **copiar `database/database.sqlite` a `storage/db-backups/` antes de
> migrar**; nunca `migrate:fresh`/`refresh`.

Añadir `scheduled_for` y `weekly_cadence` a los `$fillable`/`casts` correspondientes
(`scheduled_for => 'datetime'`).

## Estudio (Livewire + Flux) — pantalla principal

Nueva ruta y componente full-page (patrón de las demás pantallas del Estudio):

- **Ruta:** `Route::get('/studio/{account:slug}/calendario', ContentCalendar::class)->name('studio.calendar')`
  dentro del grupo `['auth','membership']` de [routes/web.php](../../routes/web.php).
- **Componente:** `app/Livewire/Studio/ContentCalendar.php` con `#[Layout('components.layouts.studio')]`.
- **Nav:** añadir `📅 Calendario` en [studio.blade.php](../../resources/views/components/layouts/studio.blade.php),
  junto a Kanban (planificación temporal vs. por estado).

### UI
- **Cuadrícula mensual/semanal** (mes por defecto; toggle semana). Cada celda = un día con sus piezas
  (mini-tarjeta: título + badge de estado + hora). Navegación mes anterior/siguiente.
- **Rail de backlog** a un lado: piezas **sin programar** (`scheduled_for` null y `status != publicada`),
  arrastrables a un día. Reutiliza el patrón **drag-drop** del Kanban (`StudioKanban`) — al soltar en un
  día se hace `update(['scheduled_for' => $fecha])`.
- **Arrastrar entre días** reprograma (`scheduled_for`). Arrastrar al backlog la desprograma (null).
- **Indicador de cadencia/huecos:** por cada semana visible, "N/objetivo programadas"; si
  `weekly_cadence` está definida y faltan, marcar el déficit (ámbar). Reaprovecha la idea de *huecos*
  de "Inicio".
- **Día vacío → acción rápida:** "Programar aquí" abre un selector de piezas del backlog, o enlaza al
  **Generador** para crear nuevas con esa fecha prepuesta.
- **Distinción visual** entre **programada** (futuro, `scheduled_for`) y **publicada** (pasada,
  `published_at`): color/título tachado o check.

### Reglas
- Escopado **siempre** por la marca activa (`$this->account->contentPieces()`).
- Editores pueden programar/reprogramar; el borrado no se expone aquí (igual que en el resto del Estudio).
- Marcar publicada sigue en la tabla de piezas; el calendario solo gestiona la **fecha prevista**.

## Admin (Filament)

El calendario gráfico vive en el Estudio. En el admin basta con exponer el campo y filtrarlo:
- En `ContentPieceForm`: `DateTimePicker::make('scheduled_for')` (sección de meta/planificación).
- En `ContentPiecesTable`: columna `scheduled_for` (ordenable) + filtro "programadas / sin programar /
  esta semana".
- En `EditAccountProfile` (Datos de la marca): `TextInput::make('weekly_cadence')->numeric()` con
  helper "Nº de piezas por semana objetivo".
- *(Opcional, fase 2)* widget calendario en el panel; no es nativo de Filament, así que no es prioritario.

## IA (opcional, fase 2)

- **Sugeridor de cadencia/relleno de huecos:** dada la `weekly_cadence` y los huecos del mes, proponer
  qué piezas del backlog (o qué ideas) colocar y cuándo. Pasa por `ContentAssistant` (queda logueado) y
  ofrece sugerencias que el usuario acepta — coherente con "IA = sugerencia, no reemplazo".

## Pruebas y QA

- **Tests** (`tests/Feature/ContentCalendarTest.php`):
  - No-miembro → 403 en `/studio/{marca}/calendario`.
  - Programar una pieza fija `scheduled_for`; reprogramar lo cambia; soltar en backlog lo pone a null.
  - Aislamiento por marca: el calendario solo lista piezas de la marca activa.
  - El cálculo de huecos respeta `weekly_cadence`.
- **QA manual:** nuevo `qa/10-calendario.md` (y enlazarlo en `qa/README.md`): navegación de meses,
  drag-drop backlog↔día↔día, indicador de cadencia, distinción programada/publicada, permisos.

## Fuera de alcance (siguientes iteraciones)
- Programación/publicación **automática** real en las plataformas (requiere integraciones/API).
- Recurrencias y plantillas de calendario.
- Vista multi-marca combinada.
