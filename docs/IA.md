# Asistente de IA (Anthropic / Claude)

> Documento **vivo**. Define cómo se integra la IA en el proyecto y el patrón de UX común a todas
> las secciones. El foco del producto se amplía hacia **asistencia con IA**: que la herramienta
> ayude a producir contenido entrenado con la metodología (guiones, ideas ganadoras, etc.).

## Principio rector: sugerir, no reemplazar

La IA **nunca** sobrescribe el contenido del usuario por su cuenta. El flujo es siempre:

1. El usuario pide sugerencias para un campo o sección (p. ej. un guión).
2. La herramienta devuelve **hasta 3 alternativas**.
3. El usuario revisa y **elige una** (o ninguna).
4. **Solo al elegir** se reescribe su contenido. Si no elige, nada cambia.

Este patrón —*hasta 3 alternativas → el usuario elige → recién entonces se aplica*— es el **comportamiento
por defecto para todas las secciones** donde se incorpore IA, salvo indicación distinta del usuario.

## Disponibilidad

La IA vive **solo en el Estudio** (Livewire + Flux). El **admin (Filament) no tiene funciones de IA**: es para
*administrar*; el **Estudio** es la suite completa de creación. La lógica de generación vive en una capa de
servicio compartida (`App\Support\Ai\*`); el Estudio aporta la UI y encola el trabajo.

## Casos de uso (orden de valor)

| # | Caso | Entrada (contexto) | Salida | Estado |
|---|------|--------------------|--------|--------|
| 1 | **Guión asistido en línea** (composer) | Idea ganadora → preguntas → mitos/verdades (multi-salto) + objetivo/formato + **plantilla Heras** de la idea + instrucciones | Hasta 3 variantes de guión (gancho · historia · moraleja · CTA) | ✅ Estudio (composer) |
| 2 | **Generador de piezas** | Idea + objetivo + formato + instrucciones + selección manual de preguntas/creencias (por seguidor) + **Ideas Ganadoras Referenciales** (HerasTemplate, 0/1/varias, filtrables por Referente) | **5** guiones → el usuario elige 1 o varios → **crea una pieza por cada uno** | ✅ Estudio (`/studio/{marca}/generador`) |
| 3 | **Generador de ideas** | Seguidor ideal → preguntas/creencias elegidas + instrucciones | Hasta 3 ideas (título · concepto · mecanismo) → el usuario guarda 1 o varias como `WinningIdea` (enlazadas a las preguntas) | ✅ Estudio (`/studio/{marca}/ideas`) |
| 4 | **Kickstart · Seguidores ideales** | Info de la marca (descripción, promesa, ofertas, cliente ideal) + instrucciones | **3** hipótesis de seguidor ideal, cada una con nivel de conciencia + 4 dolores/problemas/deseos + 4 preguntas + 4 mitos → el usuario guarda 1 o varias (crea `IdealFollower` + `Question`/`Belief`/`Pain`) | ✅ Estudio (`/studio/{marca}/kickstart`) |
| 5 | **Asistente conversacional** (chat de refinamiento) | Hilo por pieza: marca + idea + audiencia + borrador base (bloque de sistema **cacheado**) + historial + nueva instrucción ("más cálido", "más corto") | Nota de cambios + versión propuesta del guión → se aplica **solo** al pulsar "Usar esta versión" | ✅ Estudio (composer → pestaña **Asistente**) |
| 6 | *(futuro)* Lluvia de preguntas/creencias | Seguidor ideal + categoría | Hasta 3 preguntas o creencias candidatas | ⏳ |

**Caso 5 — detalle técnico:** el hilo se guarda en `piece_refinements` (un mensaje por fila). La API de Claude es
*stateless*: en cada turno se reenvía la conversación acumulada, pero el prefijo estable (rol + reglas + marca +
idea + audiencia + borrador base) se marca con **prompt caching** (`CacheControlEphemeral`), así que iterar
("más corto" → "más cálido") cuesta una fracción. La generación va en cola (`RefinePieceJob`) con polling, igual
que los demás casos. `ContentAssistant::refineScript()` construye `RefineContext` → `toSystem()` (bloque cacheado)
+ `toMessages()` (user/assistant + instrucción).

### Configuración editable

Todo el prompt es afinable desde [`config/ai.php`](../config/ai.php) **sin tocar código**:

- **Guión:** `ai.script.system.role` (rol), `ai.script.system.rules` (reglas), `ai.script.formula`
  (estructura gancho/historia/moraleja/CTA), `ai.script.suggestions` (= 5, generador) y
  `ai.script.inline_suggestions` (= 3, sugerencias en línea).
- **Ideas:** `ai.idea.system.role`, `ai.idea.system.rules`, `ai.idea.suggestions` (= 3).
- **Kickstart:** `ai.kickstart.system.role/rules`, `ai.kickstart.suggestions` (= 3), `ai.kickstart.awareness`
  (explicación de los niveles de conciencia) y `ai.kickstart.examples` (buenos/malos ejemplos de seguidor ideal).

`ContentAssistant` compone el prompt del sistema a partir de estos valores (rol + tarea + fórmula + reglas).

### Plantillas Heras en el contexto

El guión incluye la fórmula Heras (`structure`, `suggested_format`, `viral_mechanism`) **solo cuando tiene
contenido**. En el inline se toma la plantilla ligada a la idea; en el generador, además, las que el usuario
selecciona explícitamente.

Las **30 plantillas reales** (fuente: [`sources/PlantillasVictorHeras.md`](../sources/PlantillasVictorHeras.md))
se cargan con `HerasTemplateSeeder` (idempotente y **no destructivo**: solo rellena las que aún están vacías,
respeta las editadas). Para (re)cargar: `php artisan db:seed --class=HerasTemplateSeeder`.

El usuario irá afinando los **prompts** de forma progresiva (en `ContentAssistant`) y la fórmula en `config/ai.php`.

### Cómo añadir un nuevo caso (patrón)

1. Modelos de structured output (`*Variant` + `*VariantSet` en `app/Support/Ai/`) con los campos a generar.
2. Un VO de contexto (`*Context`) con `toPrompt()` desde los datos del dominio.
3. Un método `suggestX(...)` en `ContentAssistant` que reúsa `generate()` y devuelve `Suggestion[]`
   (cada `Suggestion` lleva `fields` = los campos a aplicar — uno o varios).
4. UI **en el Estudio**: componente Livewire que crea un `AiGeneration`, encola `GenerateSuggestionsJob` y
   hace polling (`wire:poll`) hasta cargar las sugerencias; modal/tarjetas Flux para elegir y aplicar.

## Arquitectura técnica

- **SDK:** PHP oficial de Anthropic — `composer require anthropic-ai/sdk`. No usar HTTP a mano salvo necesidad.
- **Modelo por defecto:** `claude-opus-4-8` (el más capaz). *Adaptive thinking* (`thinking: {type: "adaptive"}`)
  para tareas no triviales. Para tareas simples/baratas se puede bajar a `claude-haiku-4-5`, pero la
  elección del modelo es decisión del usuario, no se baja "por costo" sin pedirlo.
- **Hasta 3 alternativas en una sola llamada:** usar **structured outputs** (`output_config.format` con un
  JSON schema que devuelva un array de 1–3 sugerencias). Evita parsear texto libre y garantiza forma válida.
- **Clave de API:** **solo** por entorno → `ANTHROPIC_API_KEY` en `.env` (y `config/services.php`).
  Nunca hardcodear ni commitear la clave. En producción es un secreto gestionado (ver `PRODUCCION.md`).
- **Capa de servicio:** una clase tipo `App\Support\Ai\*` (siguiendo el patrón de `App\Support\LinkPreview`)
  que, dado un caso de uso + contexto, devuelva un array de sugerencias tipadas. Los paneles (Filament action
  / componente Livewire) solo muestran las opciones y aplican la elegida.
- **Patrón de UX reutilizable:** una "selección de sugerencias" (modal/acción con ≤3 tarjetas y un botón
  *Usar esta*) compartida por las distintas secciones, para no reimplementar el flujo cada vez.

## Notas

- **Cola asíncrona (Estudio):** el **generador de piezas** y el **guión asistido en línea** del Estudio
  generan en **segundo plano** (Job `GenerateSuggestionsJob` → registro `AiGeneration` → la UI hace polling
  con `wire:poll`). **Requiere un worker de cola corriendo**: en dev, `php artisan queue:work` (junto a
  `php artisan serve`); en producción, un supervisor (ver `PRODUCCION.md`). Sin worker, la UI se queda en
  "Generando…". `QUEUE_CONNECTION=database` (sin Redis).
- **Admin sin IA:** el panel admin **no** ofrece funciones de IA (es para administrar). Todo el flujo de
  creación asistida vive en el Estudio. `config('ai.request_timeout')` (120 s) queda como salvaguarda del
  servicio por si se invoca fuera de la cola.
- **Registro de interacciones:** **toda** llamada a la IA (guiones, ideas, Kickstart) pasa por
  `ContentAssistant::generate()`, que loguea el **prompt** (system + mensaje) y el **resultado** en un canal
  diario propio (`Log::channel('ai')` → `storage/logs/ai-YYYY-MM-DD.log`, retención `AI_LOG_DAYS`=30 días,
  definido en `config/logging.php`). También registra los fallos (timeout, error de API, respuesta
  inesperada) con su duración. Usa el driver `daily` nativo de Laravel; nada de paquetes externos.
- **Coste y latencia:** cada generación es una llamada de pago. Si la latencia molesta, baja
  `config('ai.effort')` a `medium`/`low`. Considerar un límite de uso por marca a futuro.
- **No es asesoramiento garantizado:** las sugerencias son borradores; el creador siempre decide.
- **QA:** al tocar la IA, actualizar [`qa/09-ia.md`](../qa/09-ia.md): que se ofrezcan ≤N alternativas, que
  elegir reescriba y *no elegir* no cambie nada, y que la generación (en cola) muestre estado y cargue al terminar.
