# Handoff — Sistema de Gestión de Producción de Contenido

> **Propósito de este documento:** servir como única fuente de verdad para arrancar el desarrollo del sistema en un proyecto/conversación nuevo (idealmente Claude Code). Contiene el contexto, el stack decidido, el modelo de dominio completo, los casos de uso y las decisiones abiertas.

> **Nota de implementación (2026-06):** se arrancó de cero con **Laravel 13** (en lugar de la 12 indicada
> abajo; Filament v5.6 soporta L11/12/13) + **Filament v5**, descartando un scaffold previo del Livewire
> Starter Kit. Decisiones abiertas resueltas para el P0: #5 ContentPiece→WinningIdea **nullable**;
> #1 Formato como **enum**; #3 HerasTemplate **pospuesto a P1**; #2 categorías **por marca**;
> #6 monousuario con `account_user` listo. BD de desarrollo: **SQLite**.

---

## 1. Contexto

**Marca inicial:** *El Rod y El Rol* — marca personal / personaje creador cuyo objetivo es atraer gente nueva al hobby del rol de mesa mediante contenido viral, y conducirla hacia MesasRoleras.com (explorar GameMasters, "Solicita un GM", descubrir partidas para principiantes, etc.).

**Metodología de contenido:** Víctor Heras (formación en curso). El contenido se estructura en *ideas ganadoras* derivadas de plantillas y mecanismos de viralidad.

**Quién usa el sistema:** por ahora un único creador (el dueño del proyecto, que también es el desarrollador). A futuro podría extenderse a otras marcas propias o de clientes, por lo que la **multi-cuenta es un requisito de diseño desde el día uno**, aunque el primer despliegue sea de un solo usuario.

**Qué problema resuelve el sistema:** hoy las preguntas de la audiencia, las ideas de contenido, los mitos/verdades a comunicar y las piezas a producir viven dispersos. El sistema centraliza todo ese flujo —de la investigación de audiencia a la pieza publicada y calificada— y mantiene las relaciones entre esos elementos para que el creador vea, frente a una pieza, exactamente qué pregunta responde y qué creencia refuerza o desmiente.

---

## 2. Objetivos

1. Registrar y categorizar las preguntas de cada *Seguidor Ideal* por marca.
2. Capturar *ideas ganadoras* (conceptos de contenido) y relacionarlas con las preguntas que resuelven.
3. Mantener un banco de *mitos y verdades* a desmentir/impulsar, relacionados con preguntas.
4. Gestionar la producción de *piezas de contenido* con un flujo de estados, guión estructurado y calificación.
5. Permitir trabajar varias marcas de forma aislada con la misma estructura (multi-cuenta).
6. Hacer visible, frente a una idea o una pieza, la cadena de relaciones (pregunta → mito/verdad) **sin navegación manual** (requisito de "visibilidad multi-salto").

### No-objetivos (v1)

- **Publicación automática** a redes (Instagram/TikTok/YouTube). El sistema guarda la URL manualmente; no integra APIs de publicación.
- **Editor de video / generación de assets.** Es un gestor, no una herramienta de edición.
- **Analítica de rendimiento en vivo** (vistas, likes). La "Calificación" es manual y cualitativa por ahora.
- **Colaboración multi-usuario con roles finos.** Diseñamos para que sea posible después, pero v1 es de un usuario.
- **Generación de ideas por IA dentro del sistema.** Fuera de alcance para v1.

---

## 3. Stack técnico (decidido)

| Capa | Tecnología | Notas |
|---|---|---|
| Framework | **Laravel 13** | Base del proyecto (el doc original decía 12). |
| Panel/admin | **Filament v5** | Genera el UI de gestión (resources, forms, tables, relation managers). Multi-tenancy nativa. |
| Interactividad | **Alpine.js** | Ya viene integrado en el stack TALL vía Livewire/Filament. |
| Base de datos | **SQLite** (dev) / PostgreSQL o MySQL (prod) | El esquema relacional y las N:M se diseñan a mano; Filament construye el UI encima. |

**Paquetes de apoyo sugeridos:**
- `spatie/laravel-model-states` — máquina de estados para el flujo de la pieza (opcional; alternativa: enum simple).
- `spatie/laravel-permission` — sólo cuando se incorporen roles/clientes (fase futura).
- Enums nativos de PHP para vocabularios controlados fijos (Estado, Calificación, tipo de Belief, Formato).

---

## 4. Modelo de dominio

### 4.1 Entidades y relaciones (resumen)

```
Account (tenant / marca)
 ├─ has many IdealFollower
 ├─ has many Category
 ├─ has many Question
 ├─ has many Belief
 ├─ has many WinningIdea
 └─ has many ContentPiece

IdealFollower → belongs to Account; has many Question
Category      → belongs to Account; has many Question
Question      → belongs to Account, IdealFollower, Category (nullable);
                belongs to many WinningIdea (question_winning_idea);
                belongs to many Belief (belief_question)
Belief        → belongs to Account; belongs to many Question (belief_question)
WinningIdea   → belongs to Account; belongs to many Question (question_winning_idea);
                (derivado) beliefs vía sus Questions  ← VISIBILIDAD MULTI-SALTO
                [HerasTemplate → P1]
ContentPiece  → belongs to Account; belongs to WinningIdea (NULLABLE);
                (derivado) questions y beliefs vía WinningIdea  ← VISIBILIDAD MULTI-SALTO
HerasTemplate [global, P1; seed con las 30 plantillas]
```

### 4.2 Diccionario de datos

> `account_id` está presente en todas las tablas escopadas por marca y es la columna que Filament usa para el tenant scoping. Todas las tablas llevan `id`, `created_at`, `updated_at` salvo indicación.

**accounts** (tenant): `name` string, `slug` string unique, `description` text nullable.

**ideal_followers**: `account_id` FK, `name` string, `description` text.

**categories**: `account_id` FK (por marca), `name` string, `color` string nullable.

**questions**: `account_id` FK, `ideal_follower_id` FK, `category_id` FK nullable, `body` text, `notes` text nullable.

**beliefs**: `account_id` FK, `type` enum(`myth`/`truth`), `statement` text, `stance` text nullable.

**winning_ideas**: `account_id` FK, `title` string, `concept` text, `viral_mechanism` string nullable. *(heras_template_id → P1)*

**content_pieces**: `account_id` FK, `winning_idea_id` FK **nullable** (nullOnDelete), `format` enum, `title` string, `status` enum, `hook`/`story`/`moral`/`cta` text nullable, `url` string nullable, `rating` enum nullable.

**heras_templates** (P1): `number` 1–30, `name`, `structure`, `suggested_format`, `viral_mechanism`.

**Pivotes:** `question_winning_idea` (question_id, winning_idea_id); `belief_question` (belief_id, question_id); `account_user` (account_id, user_id).

### 4.3 Vocabularios controlados (enums)

- **ContentStatus** (orden del flujo): `planificacion` → `guion_listo` → `lista_para_grabacion` → `grabada` → `editada` → `publicada`.
- **ContentRating**: `mala`, `media`, `buena`, `viral`.
- **BeliefType**: `myth` (mito), `truth` (verdad).
- **ContentFormat** (set Heras inicial): `hablando_a_camara`, `selfie`, `entrevista`, `puv`, `pov`, `personajes`, `vlog`, `documental_reto`, `hablando_a_camara_visual`.

---

## 5. Requisito clave: visibilidad multi-salto

- **Desde una Idea Ganadora**, ver los **mitos y verdades** relacionados *a través de* sus preguntas: `WinningIdea → Questions → Beliefs`.
- **Desde una Pieza de Contenido**, ver la **idea** y, a través de ella, **preguntas, mitos y verdades**: `ContentPiece → WinningIdea → Questions → (Beliefs)`.

**Implementación:** como ambos saltos son N:M, `hasManyThrough` no aplica; se usan **accessors derivados** en Eloquent (recopilar `beliefs` únicos de las preguntas relacionadas), expuestos en un **Infolist de Filament** de sólo lectura en las vistas de Idea y de Pieza.

---

## 6. Casos de uso (historias de usuario)

### A. Marcas (cuentas)
- **A1.** Crear una marca/cuenta para gestionar su contenido aislado.
- **A2.** Cambiar entre marcas, viendo sólo sus datos.
- *Criterios:* crear marca con nombre/descripción; al seleccionar marca todos los listados muestran sólo sus datos; no es posible ver/relacionar registros de otra marca.

### B. Seguidores ideales y preguntas
- **B1.** Definir Seguidores Ideales con descripción. **B2.** Registrar preguntas y asociarlas a un Seguidor Ideal. **B3.** Categorizar cada pregunta. **B4.** Filtrar/buscar preguntas por Seguidor Ideal y categoría.
- *Criterios:* una pregunta pertenece a exactamente un Seguidor Ideal; puede tener una categoría o ninguna; filtros por seguidor y categoría; vacío indicado cuando un follower no tiene preguntas.

### C. Mitos y verdades
- **C1.** Registrar mitos/verdades diferenciados por tipo. **C2.** Relacionar una pregunta con uno o varios mitos/verdades.
- *Criterios:* un Belief tiene tipo mito/verdad; N:M con preguntas; navegación bidireccional pregunta↔belief.

### D. Ideas ganadoras
- **D1.** Redactar ideas ganadoras. **D2.** Relacionar una idea con una o muchas preguntas. **D3.** Al ver una idea, ver los mitos/verdades **a través de** sus preguntas sin navegar. **D4.** *(P1)* Asociar a plantilla de Heras.
- *Criterios:* N:M idea↔preguntas; sección con beliefs derivados sin duplicados; vacío con texto guía si no hay preguntas.

### E. Piezas de contenido (producción)
- **E1.** Crear pieza ligada a una idea. **E2.** Guión en 4 campos (gancho, historia, moraleja, CTA). **E3.** Asignar formato (selección única). **E4.** Mover por estados. **E5.** Ver cascada idea→preguntas→mitos/verdades. **E6.** Guardar URL al publicar. **E7.** Calificar (Mala/Media/Buena/Viral). **E8.** *(P1)* Kanban por estado.
- *Criterios:* pieza referencia una idea (nullable, ver #5); estado del enum; cascada sólo lectura; URL/Calificación opcionales hasta publicar.

---

## 7. Requisitos por prioridad

**P0 — v1:** Multi-cuenta con aislamiento; CRUD de Seguidores/Categorías/Preguntas; CRUD Mitos/Verdades + N:M; CRUD Ideas + N:M; visibilidad multi-salto en Idea; CRUD Piezas (estado, formato, guión, URL, calificación); visibilidad multi-salto en Pieza.

**P1 — siguiente:** `HerasTemplate` + seed 30 + relación; `formats` editable por marca; kanban; filtros/búsqueda en todos los listados; dashboard de conteos.

**P2 — futuro:** multi-usuario/roles; mecanismo de viralidad estructurado; CTA controlada; métricas reales; API/frontend a medida.

---

## 8. Notas de implementación (Filament)

- **Tenancy:** `Account` como tenant del panel; `User implements HasTenants` con `belongsToMany(Account)` (tabla `account_user`); resources auto-escopados; los no escopados usan `$isScopedToTenant = false`.
- **Resources:** uno por entidad principal. `make:filament-resource X --generate` como punto de partida.
- **N:M:** relation managers o `Select` múltiples.
- **Multi-salto:** accessor + `Infolist` (sólo lectura). No usar relation manager.
- **Enums:** PHP con `HasLabel`/`HasColor`.
- **Guión:** 4 campos en un `Section`/`Fieldset`.
- **Kanban (P1):** plugin de Filament o vista Livewire a medida, agrupando por `status`.

---

## 9. Decisiones abiertas (estado P0)

1. **Formato:** enum PHP (resuelto para v1). Tabla `formats` editable → P1.
2. **Categorías:** por marca (resuelto).
3. **HerasTemplate:** pospuesto a P1.
4. **CTA:** texto libre dentro del guión (v1). Lista controlada → P2.
5. **¿Pieza sin idea?** Sí, FK `winning_idea_id` **nullable** (resuelto). La cascada queda vacía en ese caso.
6. **Multi-usuario/roles:** no en v1; `account_user` listo para no migrar después.
7. **Mecanismo de viralidad:** atributo libre por ahora; catálogo → P2.

---

## 10. Orden de construcción (fases)

- **Fase 0 — Cimientos.** Laravel 13 + Filament v5; panel `/admin`; tenancy con `Account`; usuario inicial; CRUD de `Account`.
- **Fase 1 — Audiencia.** `IdealFollower`, `Category`, `Question` con relaciones; filtros básicos.
- **Fase 2 — Conocimiento.** `Belief` + N:M; `WinningIdea` + N:M; accessor multi-salto Idea → Beliefs + Infolist.
- **Fase 3 — Producción.** `ContentPiece` con enums, formato, guión, URL; Infolist cascada Pieza → Idea → Preguntas → Mitos/Verdades.
- **Fase 4 — Productividad (P1).** `HerasTemplate` + seed; `formats` editable; kanban; filtros/búsqueda; dashboard.
- **Fase 5 — Escala (P2).** Roles/clientes; mecanismos de viralidad; CTA controlada; métricas; API/frontend.

---

## Apéndice — Relación con MesasRoleras (sólo para CTAs de contenido)

El sistema es interno y agnóstico de marca. Para *El Rod y El Rol*, las piezas dirigen a acciones reales de MesasRoleras.com: explorar GameMasters, "Solicita un GM" (ideal para grupos de amigos sin máster), seguir GMs y descubrir partidas para principiantes (gratuitas y de pago). Hoy vive como texto libre en el campo **CTA**; ver Decisión abierta #4.
