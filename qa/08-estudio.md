# 08 — Estudio (frontend a medida, Livewire + Flux)

> Acceso: panel → *Producción → ✨ Abrir Estudio*, o `/studio/el-rod-y-el-rol`.
> Requiere `npm run build` (Node ≥ 22.12) para verse con estilos.

## General

- [ ] **Tema.** El Estudio usa un **tema oscuro fijo** plomo-violáceo con **acento violeta** (sin conmutador
      claro/oscuro). Cards/header apenas más claros que el fondo, texto claro, primarios violeta. El **admin**
      (Filament) conserva su propio tema y **no** se ve afectado.
- [ ] **Acceso protegido.** Sin sesión → redirige a login. Usuario no miembro de la marca → 403.
- [ ] **Navegación** agrupada (sin emojis, iconos consistentes): **Inicio** + 4 desplegables —
      **Audiencia** (Seguidores ideales · CTAs · Kickstart),
      **Contenido** (Ideas ganadoras · Generador de ideas · Generador de piezas · Composer),
      **Planificación** (Kanban · *Calendario, próx.*), **Análisis** (*Rendimiento* · *Uso de IA*, próx.).
      El **grupo** de la sección actual se **resalta**; al abrir un menú se ve su lista. (**Generador de ideas**,
      **Generador de piezas** y **Kickstart** se detallan en [09-ia.md](09-ia.md).)
- [ ] **Lado derecho:** botón de **captura rápida (Inbox)** (icono), **selector de marca** (con miniatura)
      y **Volver al admin**. Cambiar de marca mantiene la misma sección.
- [ ] **Próximamente.** *Calendario*, *Rendimiento* y *Uso de IA* aparecen como filas **deshabilitadas**
      con etiqueta "próx." (aún no navegables; ver [../docs/roadmap/README.md](../docs/roadmap/README.md)).
- [ ] **Confirmación en acciones destructivas.** Cualquier borrado pide confirmación antes de ejecutarse:
      seguidor (avisa del borrado en cascada de sus preguntas/creencias/dolores), pregunta, creencia, dolor,
      idea ganadora, ejemplo de idea, CTA, **pieza de contenido** y captura del Inbox. *Convertir* una captura
      (→ idea/pregunta/creencia) no pide confirmación (no destruye datos; los traslada).
- [ ] **Borrar pieza (Composer).** En el **Composer**, el botón **Eliminar** (cabecera) solo aparece para **Admin**
      de la marca; pide confirmación y, tras borrar, selecciona la pieza más reciente (o vacía si no quedan).

## Ideas ganadoras (`/studio/{marca}/ideas-ganadoras`)

> Menú **Contenido → Ideas ganadoras**. CRUD completo de ideas ganadoras en el Estudio (paridad con el admin),
> con autoguardado. No confundir con **Generador de ideas** (flujo IA) ni con **Generador de piezas**.

- [ ] **Lista + nueva.** Columna izquierda con las ideas de la marca (cada una con badge de validación); **"Nueva idea"** crea y selecciona una.
- [ ] **Autoguardado.** Editar título, concepto, mecanismo o plantilla Heras persiste solo (badge **Guardado**).
- [ ] **Ejemplos reales.** Añadir/quitar URLs (Enter o botón **Añadir**). Con ≥1 ejemplo el badge de cabecera pasa a **Validada** (verde); sin ejemplos, **Pendiente de validación** (gris) — **en vivo**.
- [ ] **Seguidor ideal.** Selector de seguidor (el centro): al elegirlo, las preguntas se **acotan** a él y aparece su contexto.
- [ ] **Preguntas.** Buscador + casillas para vincular preguntas **del seguidor**; debajo aparecen los **mitos/verdades del seguidor**.
- [ ] **Borrado solo Admin.** El botón 🗑 solo aparece para **Admin** de la marca; un **Editor** no lo ve.
- [ ] **Aislamiento por marca.** Solo se listan ideas de la marca activa.

## CTAs (`/studio/{marca}/ctas`)

> Llamadas a la acción reutilizables de la marca. También gestionables en el admin (*Producción → CTAs*).

- [ ] **Alta.** El bloque "Nueva CTA" (categoría **Seguir / Palabra clave** + texto, máx. 600) crea una CTA al pulsar **Añadir CTA**.
- [ ] **Autoguardado.** Editar la categoría o el texto de una CTA existente persiste solo (recargar lo confirma); aparece el badge **Guardado**.
- [ ] **Borrado.** El botón 🗑 (con confirmación) elimina la CTA.
- [ ] **Aislamiento por marca.** Solo aparecen las CTAs de la marca activa.

## Audiencia (`/studio/{marca}/audiencia`)

> Hub para editar la audiencia desde el Estudio (también editable en el admin).

- [ ] **Lista de seguidores** a la izquierda; **"Nuevo"** crea un seguidor.
- [ ] **Seleccionar** un seguidor → editar **nombre**, **nivel de conciencia** (0–4) y **descripción**; autoguarda (indicador "Guardado", recargar confirma).
- [ ] **Preguntas / Creencias / Dolores:** cada sección lista los ítems del seguidor; editar el texto/tipo **autoguarda**; **añadir** (campo + Enter/botón) y **borrar** (✕).
- [ ] **Borrar seguidor** (con confirmación) elimina también sus preguntas/creencias/dolores.
- [ ] **Selector de marca.** Junto al nombre, un desplegable lista **las marcas del usuario** (cada una con su **miniatura/logo**, o la inicial si no tiene); cambiar de marca mantiene la sección (Inicio/Inbox/…). *(Con una sola marca aparece solo esa.)*
- [ ] **Volver al admin** (botón) regresa a `/admin`.

## Inicio (`/studio/{marca}`)

- [ ] **Totales** (piezas, ideas, preguntas, creencias).
- [ ] **Pipeline** de producción con conteo por estado.
- [ ] **Huecos por cubrir**.
- [ ] **Piezas recientes** con acceso directo.
- [ ] Botón **"Abrir composer"**.

## Inbox (`/studio/{marca}/inbox`)

- [ ] **Capturar.** Escribir una nota y pulsar **Enter** → se añade a la bandeja y la caja se limpia. Repetir varias.
- [ ] **Clasificar.** Cada captura tiene botones **→ Pregunta · → Mito · → Verdad · → Idea** y **descartar**. Convertir → crea el registro real y la captura **sale** de la bandeja.
- [ ] **Seguidor para preguntas.** El selector de seguidor aplica al convertir a Pregunta. Sin seguidores, el botón "→ Pregunta" está deshabilitado.
- [ ] **Descartar** elimina la captura (con confirmación).

## Kanban (`/studio/{marca}/kanban`)

- [ ] **Arrastrar** tarjetas entre columnas → el estado **se persiste** (recargar lo confirma). Contadores por columna se actualizan.
- [ ] Tarjeta → **"Abrir en composer →"** abre esa pieza concreta en el composer (deep-link).

## Composer (`/studio/{marca}/piezas`)

- [ ] **Lista** de piezas a la izquierda; **"Nueva"** crea un borrador.
- [ ] **Título y Estado siempre visibles.** El **Título de trabajo** y el **Estado** están arriba, fuera de las pestañas (visibles en todo momento).
- [ ] **Pestañas.** El resto se agrupa en 4 pestañas: **Datos básicos** (idea, seguidor, objetivo/formato/calificación), **Guión** (4 campos + botón IA), **RUM** (los 5 factores con sus textos guía + badge), **Producción**. La pestaña activa se resalta; cambiar de pestaña **no** pierde lo escrito.
- [ ] **Producción.** Pestaña con **Locación**, **Equipo necesario**, **Personas y personajes**, más la **URL publicada** (con vista previa) y **Marcar publicada**.
- [ ] **Seleccionar idea** → panel de **contexto en vivo** (preguntas + mitos/verdades a tratar).
- [ ] **Autoguardado.** Escribir en cualquier campo (de cualquier pestaña) → se guarda solo (recargar confirma). Indicador "Guardado"; botón **Guardar** guarda todo de una.
- [ ] **Publicación.** Pegar URL → botón **"Vista previa"** muestra la miniatura. Botón **"Marcar publicada"** pone estado Publicada + fecha; luego desaparece.
- [ ] **Evaluación RUM.** En la pestaña RUM, 5 selectores; al elegirlos, el **badge RUM** se actualiza en vivo (rojo ≤5 / amarillo 5–7 / verde >7) y se autoguarda.
- [ ] **Deep-link.** Entrar con `?piece={id}` (o desde el kanban) preselecciona esa pieza.
