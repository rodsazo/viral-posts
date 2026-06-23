# 08 — Estudio (frontend a medida, Livewire + Flux)

> Acceso: panel → *Producción → ✨ Abrir Estudio*, o `/studio/el-rod-y-el-rol`.
> Requiere `npm run build` (Node ≥ 22.12) para verse con estilos.

## General

- [ ] **Acceso protegido.** Sin sesión → redirige a login. Usuario no miembro de la marca → 403.
- [ ] **Navegación** superior: Inicio · Inbox · 👥 Audiencia · 🚀 Kickstart · Kanban · 💡 Ideas · ✨ Generador · Composer; el enlace de la sección actual se **resalta**. (**Ideas**, **Generador** y **Kickstart** se detallan en [09-ia.md](09-ia.md).)

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
- [ ] **Seleccionar idea** → panel de **contexto en vivo** (preguntas + mitos/verdades a tratar).
- [ ] **Autoguardado.** Escribir en cualquier campo (título, guión, selects) → se guarda solo (recargar confirma). Indicador "Guardado".
- [ ] **Publicación.** Pegar URL → botón **"Vista previa"** muestra la miniatura. Botón **"Marcar publicada"** pone estado Publicada + fecha; luego desaparece.
- [ ] **Evaluación RUM.** Sección con 5 selectores; al elegirlos, el **badge RUM** se actualiza en vivo (rojo ≤5 / amarillo 5–7 / verde >7) y se autoguarda.
- [ ] **Deep-link.** Entrar con `?piece={id}` (o desde el kanban) preselecciona esa pieza.
