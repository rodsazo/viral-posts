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

La asistencia debe estar disponible **tanto en el admin (Filament) como en el Estudio (Livewire + Flux)**.
La lógica de generación vive en una capa de servicio compartida; cada panel solo aporta su UI.

## Casos de uso (orden de valor)

| # | Caso | Entrada (contexto) | Salida | Estado |
|---|------|--------------------|--------|--------|
| 1 | **Guión a partir de idea clave** | Idea ganadora → preguntas → mitos/verdades (multi-salto) + objetivo/formato + **plantilla Heras** de la idea | Hasta 3 variantes de guión (gancho · historia · moraleja · CTA) | ✅ admin + Estudio |
| 2 | **Ideas ganadoras** | Preguntas elegidas + sus mitos/verdades (+ borrador) | Hasta 3 ideas (título · concepto · mecanismo de viralidad) | ✅ admin · Estudio pendiente |
| 3 | **Generador de piezas** (Estudio) | Idea + objetivo + formato + instrucciones + **Ideas Ganadoras Referenciales** (HerasTemplate, 0/1/varias, filtrables por Referente) | **5** guiones → el usuario elige 1 o varios → **crea una pieza por cada uno** con los parámetros del paso 1 | ✅ Estudio (`/studio/{marca}/generador`) |
| 4 | *(futuro)* Lluvia de preguntas/creencias | Seguidor ideal + categoría | Hasta 3 preguntas o creencias candidatas | ⏳ |

El **generador de piezas** vive **solo en el Estudio** (el admin se mantiene esbelto, para administrar; el
Estudio es la herramienta de creación). El admin conserva las sugerencias en línea de guión/idea.

### Configuración editable

Todo el prompt es afinable desde [`config/ai.php`](../config/ai.php) **sin tocar código**:

- **Guión:** `ai.script.system.role` (rol), `ai.script.system.rules` (reglas), `ai.script.formula`
  (estructura gancho/historia/moraleja/CTA), `ai.script.suggestions` (= 5, generador) y
  `ai.script.inline_suggestions` (= 3, sugerencias en línea).
- **Ideas:** `ai.idea.system.role`, `ai.idea.system.rules`, `ai.idea.suggestions` (= 3).

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
4. UI: en el admin, `SuggestionAction::make(name, generator)`; en el Estudio, método Livewire + modal Flux.

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

- **Coste y latencia:** cada generación es una llamada de pago. Conviene *streaming* para salidas largas y
  manejar errores/limites (429/5xx) con el reintento del SDK. Considerar un límite de uso por marca a futuro.
- **No es asesoramiento garantizado:** las sugerencias son borradores; el creador siempre decide.
- **QA:** cuando se implemente cada caso, añadir/actualizar el caso correspondiente en [`/qa/`](../qa/README.md)
  (p. ej. `qa/09-ia.md`): que se ofrezcan ≤3 alternativas, que elegir reescriba y *no elegir* no cambie nada,
  y que funcione igual en admin y en Estudio.
