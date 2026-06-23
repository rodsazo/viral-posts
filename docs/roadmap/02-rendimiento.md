# Spec — Seguimiento de rendimiento (cerrar el bucle)

> Estado: **propuesta** (sin implementar). Prioridad 🔴. Parte de [README.md](README.md), fases 7-8 del ciclo.

## Qué

Registrar los **resultados reales** de cada pieza publicada (vistas, guardados, compartidos, seguidores
ganados, respuestas a la palabra clave…) y usarlos para **rankear** qué ganchos, ideas, CTAs y niveles
de conciencia funcionan — y para **contrastar el RUM previsto con el resultado real**.

## Por qué

Es el eslabón que falta del ciclo. Toda la metodología (Heras) gira en torno a la **viralidad**, pero
hoy el **RUM** es una *predicción sin marcador*. Sin datos reales se puede generar para siempre sin
saber **qué funciona**, lo que vacía de valor estratégico a toda la planificación. Cerrar el bucle:
- rankea `HookTemplate` por conversión real,
- retira `WinningIdea` muertas y dobla la apuesta por las ganadoras,
- mide la conversión de cada **CTA** (sobre todo las de palabra clave),
- **calibra el RUM** (predicho vs. real) y, más adelante, alimenta a la IA con *ganadores* como ejemplos.

## Atribución: el prerrequisito (¡importante!)

Para atribuir rendimiento a un gancho/CTA, la pieza tiene que **recordar su procedencia**. Hoy **no la
guarda**: el Generador envía los ganchos y la CTA elegida a la IA como *texto del prompt*, pero la pieza
solo persiste el `hook`/`cta` **resultantes** (cadenas), sin FK. La idea ganadora sí está enlazada
(`winning_idea_id`). Falta enlazar gancho y CTA:

1. **`content_pieces.hook_template_id`** — `foreignId` nullable, `nullOnDelete`, a `hook_templates`.
2. **`content_pieces.content_cta_id`** — `foreignId` nullable, `nullOnDelete`, a `content_ctas`.

Y **rellenarlos al crear** en `PieceGenerator::createPieces()` (con el gancho/CTA que guiaron esa
variante). Sin esto, la analítica solo puede agregar por idea/objetivo/formato, no por gancho ni CTA.

> Nota: el Generador hoy permite *varios* ganchos (uno por variante). Al crear cada pieza ya se conoce
> el gancho que la guió → guardar **ese** en `hook_template_id`. La CTA es única → directa.

## Modelo de datos (migraciones **aditivas**)

### 1. Plataforma (dimensión nueva)
`app/Enums/Platform.php` (`HasLabel`, `HasColor`): `TikTok`, `Instagram`, `YouTube`, `X`, `Facebook`,
`LinkedIn`. Reutilizable luego para repurposing (fase 6 del README).

### 2. Métricas por pieza (serie temporal)
Tabla **`content_piece_metrics`** — un *snapshot* por pieza/plataforma/fecha (permite ver crecimiento;
el caso "último valor" es un `latestOfMany`):

```
Schema::create('content_piece_metrics', function (Blueprint $t) {
    $t->id();
    $t->foreignId('account_id')->constrained()->cascadeOnDelete();
    $t->foreignId('content_piece_id')->constrained()->cascadeOnDelete();
    $t->string('platform');                 // enum Platform
    $t->string('source')->default('manual'); // manual | csv | api:<x>
    $t->date('captured_at');
    $t->unsignedBigInteger('views')->default(0);
    $t->unsignedBigInteger('likes')->default(0);
    $t->unsignedBigInteger('comments')->default(0);
    $t->unsignedBigInteger('shares')->default(0);
    $t->unsignedBigInteger('saves')->default(0);
    $t->unsignedBigInteger('follows_gained')->default(0);
    $t->unsignedBigInteger('link_clicks')->default(0);
    $t->unsignedBigInteger('keyword_replies')->default(0); // conversión de Keyword CTA
    $t->json('extra')->nullable();          // métricas específicas de plataforma
    $t->timestamps();
    $t->unique(['content_piece_id', 'platform', 'captured_at']);
});
```

Modelo `ContentPieceMetric` (escopado por `account_id`), relaciones `account()`, `contentPiece()`;
`ContentPiece::metrics(): HasMany` + `latestMetric()` (`latestOfMany('captured_at')`).

### 3. Línea base y nivel de rendimiento (RUM real)
Para comparar peras con peras entre marcas y épocas, derivar un **nivel de rendimiento** relativo a la
**mediana de vistas de la marca** (no un absoluto):
- `app/Support/PiecePerformance.php`: KPIs derivados (engagement rate, save rate, follow-conversion,
  keyword-conversion) y un `tier()` **Alto/Medio/Bajo** según vistas vs. mediana de la marca — espejo
  del patrón `Rum` (`app/Support/Rum.php`), para poder cruzar **RUM predicho ↔ rendimiento real**.

## Captura de datos (de manual a automático)

- **Fase 1 — manual:** formulario para introducir un snapshot. Lo natural es hacerlo desde el **Composer**
  (en la pieza, sección "Rendimiento": plataforma + métricas) y/o desde la nueva pantalla de analítica.
- **Fase 2 — CSV:** import por marca (`source = csv`) para cargar exportaciones de las plataformas.
- **Fase 3 — API:** integraciones por plataforma (`source = api:tiktok`…). **Fuera de alcance ahora**;
  el esquema (`source`, `platform`, `extra`) ya lo contempla.

## Estudio (Livewire + Flux) — pantalla de analítica

- **Ruta:** `/studio/{account:slug}/rendimiento` → `app/Livewire/Studio/PerformanceDashboard.php`
  (`#[Layout('components.layouts.studio')]`). **Nav:** `📊 Rendimiento`.
- **Contenido:**
  - **Top piezas** por vistas/engagement (rango de fechas), con su estado y enlace.
  - **Leaderboard de ganchos:** `HookTemplate` rankeados por vistas/engagement medios de las piezas que
    los usaron (vía `hook_template_id`). Igual para **CTAs** (conversión, sobre todo `keyword_replies`).
  - **ROI de ideas:** `WinningIdea` por rendimiento agregado de sus piezas.
  - **Calibración del RUM:** dispersión RUM predicho vs. `tier` real → ver si el RUM acierta.
  - **Entrada rápida** de métricas (o enlace al Composer).
- Escopado por marca; Editores pueden introducir métricas.

## Admin (Filament)

- **RelationManager** "Métricas" en `ContentPieceResource` (alta/edición de snapshots) **o** un
  `ContentPieceMetricResource` ligero (grupo *Producción*), con borrado restringido a Admin
  (`RestrictsDeletionToAdmins`).
- **Widgets** de panel: top piezas del mes, follows ganados, conversión de Keyword CTA.
- En `ContentPieceForm`/tabla: mostrar `latestMetric` (vistas) como columna ordenable y filtro por `tier`.

## IA (fase 2 — el pago del bucle)

- **Aprender de los ganadores:** alimentar al `ContentAssistant` con las piezas de mayor `tier` como
  ejemplos *few-shot* al generar guiones/ideas. Cierra 7→8→2 del ciclo (medir → aprender → idear mejor).
- Queda **logueado** por el canal de IA existente (ver [../IA.md](../IA.md)).

## Pruebas y QA

- **Tests** (`tests/Feature/PerformanceTest.php`):
  - `createPieces()` rellena `hook_template_id`/`content_cta_id` con la procedencia correcta.
  - Alta de un `ContentPieceMetric`; `latestMetric()` devuelve el snapshot más reciente.
  - `PiecePerformance::tier()` clasifica según la mediana de la marca; KPIs derivados correctos.
  - Leaderboard de ganchos agrega solo piezas de la marca activa (aislamiento).
  - No-miembro → 403 en `/studio/{marca}/rendimiento`.
- **QA manual:** nuevo `qa/11-rendimiento.md` (enlazado en `qa/README.md`): introducir métricas, ver
  leaderboards de ganchos/CTAs, calibración del RUM, permisos.

## Fuera de alcance (siguientes iteraciones)
- Integraciones API con las plataformas (captura automática).
- Atribución de **ventas/ingresos** (requeriría enlazar con el embudo de CTA / lead magnets, fase 4 del README).
- Benchmarking entre marcas.

## Dependencias
- La **atribución** (FKs `hook_template_id`/`content_cta_id` + relleno en `createPieces()`) debe ir
  **primero**; sin ella, los leaderboards de gancho/CTA no son posibles.
- Se beneficia del **Calendario** ([01-calendario.md](01-calendario.md)) para cruzar *planificado vs.
  publicado vs. rendimiento*, pero no depende de él.
