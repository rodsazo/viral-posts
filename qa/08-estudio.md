# 08 — Estudio (frontend a medida, Livewire + Flux)

> Acceso: en el admin, botón **"Abrir Estudio"** en la **barra superior** (a la izquierda del buscador), o `/studio/el-rod-y-el-rol`.
> Requiere `npm run build` (Node ≥ 22.12) para verse con estilos.

## General

- [ ] **Tema.** El Estudio usa un **tema oscuro fijo** plomo-violáceo con **acento violeta** (sin conmutador
      claro/oscuro). Cards/header apenas más claros que el fondo, texto claro, primarios violeta. El **admin**
      (Filament) conserva su propio tema y **no** se ve afectado.
- [ ] **Acceso protegido.** Sin sesión → redirige a login. Usuario no miembro de la marca → 403.
- [ ] **Navegación** agrupada (sin emojis, iconos consistentes): el rótulo **"Estudio"** (arriba a la izquierda)
      es el **enlace a la portada** (ya **no** hay item "Inicio") + 3 desplegables —
      **Audiencia** (Seguidores ideales · CTAs · Kickstart),
      **Contenido** (Ideas ganadoras · Ideas Referenciales · Generador de ideas · Generador de piezas · Composer),
      **Planificación** (Kanban · Periodos · *Calendario, próx.*).
      *(El grupo **Análisis** está **oculto temporalmente**.)*
      El **grupo** de la sección actual se **resalta**; al abrir un menú se ve su lista. (**Generador de ideas**,
      **Generador de piezas** y **Kickstart** se detallan en [09-ia.md](09-ia.md).)
- [ ] **Lado derecho:** botón de **captura rápida (Inbox)** (icono), **selector de periodo** (planificación),
      **selector de marca** (con miniatura) y **Volver al admin**. Cambiar de marca mantiene la misma sección.
- [ ] **Selector de periodo (cabecera).** Muestra el **periodo activo** + un punto de color por su estado
      (verde Publicado / gris Borrador). El desplegable lista los periodos de la marca, permite **crear uno nuevo**
      (campo + Enter/+) que queda activo al instante, y enlaza a **Gestionar periodos**. El periodo activo se
      recuerda **por marca** y al cambiarlo **refiltran** el Composer y el Kanban (y las piezas nuevas se le asignan).
- [ ] **Próximamente.** *Calendario* (en Planificación) aparece como fila **deshabilitada** con etiqueta "próx."
      (aún no navegable; ver [../docs/roadmap/README.md](../docs/roadmap/README.md)). *(Rendimiento/Uso de IA viven
      en el grupo Análisis, hoy oculto.)*
- [ ] **Sin selección al abrir.** Las pantallas maestro-detalle (**Audiencia**, **Ideas ganadoras**, **Composer**)
      abren **sin ningún elemento seleccionado** (estado vacío con su mensaje). El usuario elige uno de la lista.
      *(El Composer sí preselecciona si llega por deep-link `?piece={id}`, p. ej. desde el Kanban.)*
- [ ] **Confirmación en acciones destructivas.** Cualquier borrado pide confirmación antes de ejecutarse:
      seguidor (avisa del borrado en cascada de sus preguntas/creencias/dolores), pregunta, creencia, dolor,
      idea ganadora, ejemplo de idea, CTA, **pieza de contenido** y captura del Inbox. *Convertir* una captura
      (→ idea/pregunta/creencia) no pide confirmación (no destruye datos; los traslada).
- [ ] **Borrar pieza (Composer).** En el **Composer**, el botón **Eliminar** (cabecera) solo aparece para **Admin**
      de la marca; pide confirmación y, tras borrar, selecciona la pieza más reciente (o vacía si no quedan).

## Ideas ganadoras (`/studio/{marca}/ideas-ganadoras`)

> Menú **Contenido → Ideas ganadoras**. CRUD completo de ideas ganadoras en el Estudio (paridad con el admin),
> con autoguardado. No confundir con **Generador de ideas** (flujo IA) ni con **Generador de piezas**.

- [ ] **Lista + nueva.** Columna izquierda con las ideas de la marca. Cada tarjeta muestra el **título** y, debajo,
      un tag de **Estado** (Borrador/Hipótesis/Fija/Descartada, con color e icono), el **Seguidor ideal** y el tag
      de **validación** (icono verde validada / gris pendiente). **"Nueva idea"** crea y selecciona una (nace en **Borrador**).
- [ ] **Filtro por seguidor.** Un desplegable **"Todos los seguidores"** filtra la lista por seguidor ideal.
- [ ] **Filtro por estado.** Otro desplegable filtra por estado. Por defecto (**"Activas (sin descartadas)"**) **oculta
      las descartadas**; se puede elegir un estado concreto o **"Todas (incl. descartadas)"**.
- [ ] **Orden por estado.** La lista se ordena **Fija → Hipótesis → Borrador → Descartada** (y por título dentro de
      cada grupo): las probadas (Fijas) salen arriba.
- [ ] **Estado (editor).** En el editor, un selector **Estado** (Borrador → Hipótesis → Fija/Descartada) **autoguarda**.
      Recuerda: una idea **validada** (con ejemplos reales) igual nace en Borrador; "validación" y "estado" son cosas distintas.
- [ ] **Autoguardado.** Editar título, concepto, mecanismo o plantilla Heras persiste solo (badge **Guardado**).
- [ ] **Ejemplos reales.** Añadir/quitar URLs (Enter o botón **Añadir**). Con ≥1 ejemplo el badge de cabecera pasa a **Validada** (verde); sin ejemplos, **Pendiente de validación** (gris) — **en vivo**.
- [ ] **Seguidor ideal.** Selector de seguidor (el centro): al elegirlo, las preguntas se **acotan** a él y aparece su contexto.
- [ ] **Preguntas.** Buscador + casillas para vincular preguntas **del seguidor**; debajo aparecen los **mitos/verdades del seguidor**.
- [ ] **Importadas.** Las ideas traídas desde *Ideas Referenciales* muestran una etiqueta **Importada** (violeta)
      con el referente de origen en el tooltip.
- [ ] **Campos ocultos (por ahora).** En el editor **no** aparecen **Mecanismo de viralidad** ni **Plantilla Heras**
      (ocultos hasta aprovechar mejor esas relaciones; el dato sigue en BD).
- [ ] **Borrado solo Admin.** El botón 🗑 solo aparece para **Admin** de la marca; un **Editor** no lo ve.
- [ ] **Aislamiento por marca.** Solo se listan ideas de la marca activa.

## Ideas Referenciales (`/studio/{marca}/ideas-referenciales`)

> Menú **Contenido → Ideas Referenciales**. Catálogo **global** de ideas ganadoras de referencia (plantillas
> Heras) para **importar** a la marca como Ideas Ganadoras regulares.

- [ ] **Catálogo en tarjetas.** Cada idea referencial se muestra como tarjeta con nombre, **referente** y **nicho**,
      formato sugerido, estructura, mecanismo y el nº de **URLs de referencia**.
- [ ] **Filtros.** **Buscador de texto** (busca en título, mecanismo, formato y estructura), filtro por **Referente**
      y por **Nicho** del referente (combinables).
- [ ] **Selección múltiple → Importar.** Marcar varias y pulsar **"Importar N ideas"** (pide confirmación) crea una
      Idea Ganadora por cada una en la marca y redirige a *Ideas ganadoras* con aviso.
- [ ] **Resultado de la importación.** Cada idea importada nace en **Borrador**, con etiqueta **Importada**, su
      **referente viral** y **todas las URLs** de referencia (principal + adicionales) copiadas como ejemplos reales
      (por lo que aparece como **Validada**).

## CTAs (`/studio/{marca}/ctas`)

> Llamadas a la acción reutilizables de la marca. También gestionables en el admin (*Producción → CTAs*).

- [ ] **Alta.** El bloque "Nueva CTA" (categoría **Seguir / Palabra clave** + texto, máx. 600) crea una CTA al pulsar **Añadir CTA**.
- [ ] **Autoguardado.** Editar la categoría o el texto de una CTA existente persiste solo (recargar lo confirma); aparece el badge **Guardado**.
- [ ] **Borrado.** El botón 🗑 (con confirmación) elimina la CTA.
- [ ] **Aislamiento por marca.** Solo aparecen las CTAs de la marca activa.

## Ganchos (`/studio/{marca}/ganchos`)

> Menú **Contenido → Ganchos**. CRUD de los ganchos **propios de la marca** (autoguardado, maestro-detalle).
> Los ganchos **globales de referencia** se gestionan en el admin (*Referencia → Ganchos*) y aquí **no** se listan,
> pero **sí** están disponibles en el Generador de piezas junto a los de la marca.

- [ ] **Lista + nuevo.** Columna izquierda con los ganchos de la marca (icono + nombre); **"Nuevo gancho"** crea y selecciona uno.
- [ ] **Editor con autoguardado.** Nombre, **Referente viral (opcional)**, **Ícono** (desplegable FontAwesome con vista previa), **Objetivo**, **Ejemplo**, **Notas** y **Ejemplos reales (URLs)**; editar persiste solo (badge **Guardado**).
- [ ] **Borrado solo Admin.** El botón 🗑 (con confirmación) solo aparece para **Admin** de la marca.
- [ ] **Aislamiento.** Solo aparecen los ganchos de la marca activa (ni globales ni de otras marcas).
- [ ] **Sin selección al abrir** (estado vacío hasta elegir uno).

## Audiencia (`/studio/{marca}/audiencia`)

> Hub para editar la audiencia desde el Estudio (también editable en el admin).

- [ ] **Lista de seguidores** a la izquierda; **"Nuevo"** crea un seguidor.
- [ ] **Seleccionar** un seguidor → editar **nombre**, **nivel de conciencia** (0–4) y **descripción**; autoguarda (indicador "Guardado", recargar confirma).
- [ ] **Preguntas / Creencias / Dolores:** cada sección lista los ítems del seguidor; editar el texto/tipo **autoguarda**; **añadir** (campo + Enter/botón) y **borrar** (✕).
- [ ] **Borrar seguidor** (con confirmación) elimina también sus preguntas/creencias/dolores.
- [ ] **Selector de marca.** Junto al nombre, un desplegable lista **las marcas del usuario** (cada una con su **miniatura/logo**, o la inicial si no tiene); cambiar de marca mantiene la sección (Inicio/Inbox/…). *(Con una sola marca aparece solo esa.)*
- [ ] **Volver al admin** (botón) regresa a `/admin`.

## Inicio (`/studio/{marca}`)

- [ ] **Ideas Fijas por explotar.** Si hay un periodo activo y existen ideas **Fijas**, arriba aparece un aviso:
      las **Fijas sin contenido en el periodo activo** (con sus títulos y botón **Generar contenido**). Si todas las
      Fijas ya tienen contenido en ese periodo, muestra un mensaje verde de "todo cubierto". Sin Fijas, no aparece.
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

## Periodos (`/studio/{marca}/periodos`)

> Menú **Planificación → Periodos**. Ventanas de planificación de contenido (p. ej. «Julio 2026»). El **periodo
> activo** se elige en el selector de la cabecera; aquí se gestionan (renombrar, estado, borrar).

- [ ] **Tablero público de la marca.** Arriba, un bloque violeta muestra el **enlace público de la marca**
      (`/m/{token}`) con **Copiar**/**Abrir** (ver *Tablero público de la marca*).
- [ ] **Alta.** Campo "Nuevo periodo" + **Añadir periodo** crea uno (nace en **Borrador**).
- [ ] **Autoguardado.** Editar el **nombre** o el **estado** (Borrador/Publicado) de un periodo persiste solo (badge **Guardado**).
- [ ] **Estado y URL pública.** Solo con el periodo en **Publicado** sus piezas (que estén «Lista para grabación»
      en adelante) son accesibles por la **URL pública** (ver *Vista pública del cliente*).
- [ ] **Conteo.** Cada periodo muestra cuántas **piezas** tiene asignadas.
- [ ] **Borrado solo Admin.** El botón 🗑 (con confirmación) solo aparece para **Admin**; al borrar, sus piezas
      **quedan sin periodo** (no se borran).

## Kanban (`/studio/{marca}/kanban`)

- [ ] **Solo el periodo activo.** El tablero muestra **únicamente las piezas del periodo seleccionado** en la
      cabecera (al cambiar de periodo, se refiltra). Sin periodos, muestra las piezas "sin periodo".
- [ ] **Arrastrar** tarjetas entre columnas → el estado **se persiste** (recargar lo confirma). Contadores por columna se actualizan.
- [ ] Tarjeta → **"Abrir en composer →"** abre esa pieza concreta en el composer (deep-link).

## Composer (`/studio/{marca}/piezas`)

- [ ] **Lista** de piezas a la izquierda; **"Nueva"** crea un borrador. El **tag de estado** de cada pieza usa el
      **mismo color que su columna en el Kanban** (Borrador=plomo, Planificación=violeta, Guión listo=azul, Lista
      para grabación=cian, Grabada=ámbar, Editada=rosa, Publicada=verde) para escanear de un vistazo.
- [ ] **Solo el periodo activo.** Bajo "Piezas" se ve el **periodo activo**; la lista muestra **solo las piezas de
      ese periodo** y **"Nueva"** las crea **dentro** de él. Sin periodos, muestra las "sin periodo" y enlaza a crear uno.
- [ ] **Filtro por estado.** Sobre la lista, un desplegable **"Todos los estados"** filtra las piezas por estado
      (Borrador, Planificación, …). Sin piezas en ese estado: "No hay piezas en este estado".
- [ ] **Título y Estado siempre visibles.** El **Título de trabajo** y el **Estado** están arriba, fuera de las pestañas (visibles en todo momento).
- [ ] **Pestañas.** El resto se agrupa en 4 pestañas: **Datos básicos** (idea, seguidor, objetivo/formato/calificación), **Guión** (4 campos + botón IA), **RUM** (los 5 factores con sus textos guía + badge), **Producción**. La pestaña activa se resalta; cambiar de pestaña **no** pierde lo escrito.
- [ ] **Producción.** Pestaña con un bloque **"Enlace para el cliente"** (vista pública), **Locación**, **Equipo necesario**, **Personas y personajes**, **Notas para el cliente**, más la **URL publicada** (con vista previa) y **Marcar publicada**.
- [ ] **Enlace para el cliente.** En la pestaña **Producción**, un bloque violeta muestra la **URL pública** de la pieza con botones **Copiar** (cambia a "¡Copiado!") y **Abrir** (nueva pestaña). Solo aparece con una pieza seleccionada.
- [ ] **Botón Compartir (cabecera).** En la cabecera del Composer, entre **Eliminar** y **Guardar**, un botón **Compartir** copia la URL pública al portapapeles (cambia a "¡Copiado!" ~1,5 s). Solo aparece con una pieza seleccionada.
- [ ] **Seleccionar idea** → panel de **contexto en vivo** (preguntas + mitos/verdades a tratar).
- [ ] **Autoguardado.** Escribir en cualquier campo (de cualquier pestaña) → se guarda solo (recargar confirma). Indicador "Guardado"; botón **Guardar** guarda todo de una.
- [ ] **Publicación.** Pegar URL → botón **"Vista previa"** muestra la miniatura. Botón **"Marcar publicada"** pone estado Publicada + fecha; luego desaparece.
- [ ] **Evaluación RUM.** En la pestaña RUM, 5 selectores; al elegirlos, el **badge RUM** se actualiza en vivo (rojo ≤5 / amarillo 5–7 / verde >7) y se autoguarda.
- [ ] **Deep-link.** Entrar con `?piece={id}` (o desde el kanban) preselecciona esa pieza.

## Vista pública del cliente (`/p/{token}`)

> Página **sin login** para compartir una pieza con un cliente externo: que **entienda** la propuesta y
> **valide el guión**. Diseño amable (tipografía grande, tarjetas), no parece un panel de admin. El enlace se
> copia desde el **Composer → pestaña Producción → "Enlace para el cliente"**.

- [ ] **Acceso público condicionado.** `/p/{token}` solo es accesible si la pieza está **«Lista para grabación»
      en adelante** (incluye Grabada/Editada/Publicada) **Y** su **periodo está «Publicado»**. En cualquier otro
      caso (estado anterior, periodo en Borrador o pieza sin periodo) o **token inexistente** → **404**. Los tokens
      son inadivinables (~40 caracteres).
- [ ] **Contenido completo.** Se ven, con fuentes grandes: la **marca** (logo + nombre), el **título**, los chips
      de **estado/objetivo/formato** (la **pastilla de estado** usa el mismo color que el Kanban), **la idea**
      (título + concepto), **ejemplos reales** de la idea (si tiene, como enlaces "ya funcionó en la vida real"),
      **¿a quién le hablamos?** (seguidor + nivel + descripción), **el guión** en 4 tarjetas de colores
      (gancho/historia/moraleja/CTA), las **notas para el cliente** y **cómo se grabará** (locación/equipo/personas).
      Los campos vacíos **no** muestran tarjeta.
- [ ] **Pieza vacía.** Si la pieza aún no tiene idea ni guión, aparece un **estado amable** ("se está cocinando")
      en vez de tarjetas vacías.
- [ ] **No indexable.** La página lleva `noindex,nofollow` (no pensada para buscadores).

## Tablero público de la marca (`/m/{token}`)

> Página **sin login** para el cliente: un tablero de solo lectura del **último periodo Publicado** de la marca.
> Mismos lineamientos visuales que la vista pública de pieza (fuentes grandes, buen contraste, tarjetas). El
> enlace se copia desde **Planificación → Periodos → "Tablero público de la marca"**.

- [ ] **Sin periodo publicado.** Si la marca no tiene ningún periodo **Publicado**, muestra el mensaje grande
      **"No hay periodos de trabajo abiertos"**. (Un periodo en Borrador **no** cuenta.)
- [ ] **Con periodo publicado.** Muestra el **nombre del periodo** y un **kanban de solo 4 columnas**: *Lista para
      grabación*, *Grabada*, *Editada*, *Publicada* (con su color y emoji). Solo aparecen las piezas de **ese**
      periodo en **esos** estados (las anteriores a "Lista para grabación" no se ven).
- [ ] **Último publicado.** Si hay varios periodos Publicados, se muestra **el más reciente**.
- [ ] **Tarjetas enlazadas.** Cada tarjeta (título + idea + "Ver detalles →") es un **enlace a la vista pública de
      esa pieza** (`/p/{token}`).
- [ ] **Token y 404.** El acceso es por token inadivinable; un **token inexistente** da **404**. Lleva `noindex,nofollow`.
