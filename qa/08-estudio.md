# 08 — Estudio (frontend a medida, Livewire + Flux)

> Acceso: en el admin, botón **"Abrir Estudio"** en la **barra superior** (a la izquierda del buscador), o `/studio/el-rod-y-el-rol`.
> Requiere `npm run build` (Node ≥ 22.12) para verse con estilos.

## General

- [ ] **Tema.** El Estudio usa un **tema oscuro fijo** plomo-violáceo con **acento violeta** (sin conmutador
      claro/oscuro). Cards/header apenas más claros que el fondo, texto claro, primarios violeta. El **admin**
      (Filament) conserva su propio tema y **no** se ve afectado.
- [ ] **Acceso protegido.** Sin sesión → redirige a login. Usuario no miembro de la marca → 403.
- [ ] **Navegación** agrupada (sin emojis, iconos consistentes): el rótulo **"Estudio"** (arriba a la izquierda)
      es el **enlace a la portada** (ya **no** hay item "Inicio") + desplegables —
      **Marca** (Diseño de Marca — ver [10-marca.md](10-marca.md); Personajes de marca y Generador de personajes — ver [11-personajes.md](11-personajes.md)),
      **Audiencia** (Seguidores ideales · CTAs · Kickstart),
      **Contenido** (Ideas ganadoras · Ideas Referenciales · Generador de ideas · Generador de piezas · Composer),
      **Planificación** (Kanban · Periodos · *Calendario, próx.*),
      **Análisis** (Uso de IA · *Rendimiento, próx.*).
      El **grupo** de la sección actual se **resalta**; al abrir un menú se ve su lista. (**Generador de ideas**,
      **Generador de piezas** y **Kickstart** se detallan en [09-ia.md](09-ia.md).)
- [ ] **Lado derecho:** botón de **captura rápida (Inbox)** (icono), **selector de periodo** (planificación),
      **selector de marca** (con miniatura) y **Volver al admin**. Cambiar de marca mantiene la misma sección.
- [ ] **Selector de periodo (cabecera).** Muestra el **periodo activo** + un punto de color por su estado
      (verde Publicado / gris Borrador). El desplegable lista los periodos de la marca, permite **crear uno nuevo**
      (campo + Enter/+) que queda activo al instante, y enlaza a **Gestionar periodos**. El periodo activo se
      recuerda **por marca** y al cambiarlo **refiltran** el Composer y el Kanban (y las piezas nuevas se le asignan).
- [ ] **Modo "Sin periodo".** El desplegable incluye, arriba, la opción **Sin periodo** (con un badge ámbar con el
      **nº de piezas sin asignar**). Al elegirla, el Composer y el Kanban muestran **solo las piezas sin periodo**
      (`period_id` nulo), que de otro modo quedarían invisibles al haber un periodo activo. Sirve para **rescatarlas**
      y asignarles un periodo desde el Composer (ver abajo).
- [ ] **Próximamente.** *Calendario* (en Planificación) y *Rendimiento* (en Análisis) aparecen como filas
      **deshabilitadas** con etiqueta "próx." (aún no navegables; ver [../docs/roadmap/README.md](../docs/roadmap/README.md)).
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

> La idea ganadora es una **descripción de FORMATO** (título + estructura/concepto + estado + ejemplos): habla del
> formato del video, no del video en sí. **Ya NO** se relaciona con seguidor, preguntas ni mitos (eso se elige al
> generar la pieza).

- [ ] **Lista + nueva.** Columna izquierda con las ideas de la marca. Cada tarjeta muestra el **título** y, debajo,
      un tag de **Estado** (Borrador/Hipótesis/Fija/Descartada, con color e icono), una **píldora de piezas** (icono de
      film + nº de piezas de esa idea **en el periodo activo**; violeta si hay, gris si 0) y el tag de **validación**
      (icono verde validada / gris pendiente). **"Nueva idea"** crea y selecciona una (nace en **Borrador**). *(Sin tag ni
      filtro de seguidor.)*
- [ ] **Contador de piezas por periodo.** La píldora de film cuenta las piezas de esa idea **en el periodo activo** de
      la cabecera (cambiar de periodo recalcula). Un texto bajo los filtros indica en qué periodo se cuentan.
- [ ] **Filtro por estado.** Un desplegable filtra por estado. Por defecto (**"Activas (sin descartadas)"**) **oculta
      las descartadas**; se puede elegir un estado concreto o **"Todas (incl. descartadas)"**.
- [ ] **Filtro por piezas.** Un segundo desplegable filtra por presencia de piezas en el periodo activo:
      **Con y sin piezas** (todas), **Con piezas este periodo**, **Sin piezas este periodo**. Se combina con el filtro de estado.
- [ ] **Orden por estado.** La lista se ordena **Fija → Hipótesis → Borrador → Descartada** (y por título dentro de
      cada grupo): las probadas (Fijas) salen arriba.
- [ ] **Estado (editor).** En el editor, un selector **Estado** (Borrador → Hipótesis → Fija/Descartada) **autoguarda**.
      Recuerda: una idea **validada** (con ejemplos reales) igual nace en Borrador; "validación" y "estado" son cosas distintas.
- [ ] **Concepto / estructura.** Campo de texto donde se describe el **formato** (estructura, condiciones,
      consideraciones para hacer el video). Autoguarda.
- [ ] **Ejemplos reales.** Añadir/quitar URLs (Enter o botón **Añadir**). Con ≥1 ejemplo el badge de cabecera pasa a **Validada** (verde); sin ejemplos, **Pendiente de validación** (gris) — **en vivo**.
- [ ] **Guardar (cabecera).** Los campos autoguardan al salir, pero un botón **Guardar** (arriba a la derecha, junto a *Crear pieza*) guarda todo de una y muestra el badge **Guardado**.
- [ ] **Piezas de la idea (periodo activo).** Debajo del editor, un panel lista las **piezas de esa idea en el periodo activo** con su estado; cada una **enlaza al Composer** (abre esa pieza). Muestra el periodo y el conteo. Sin piezas: enlace **"Crea una"** (crea una pieza desde la idea y salta al Composer).
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
- [ ] **Pestañas.** El resto se agrupa en pestañas: **Datos básicos** (idea, seguidor, periodo, objetivo/formato/calificación), **Guión** (4 campos + botón IA), **Asistente** (chat de IA — solo si hay clave configurada), **RUM** (los 5 factores con sus textos guía + badge), **Producción**. La pestaña activa se resalta; cambiar de pestaña **no** pierde lo escrito.
- [ ] **Producción.** Pestaña con un bloque **"Enlace para el cliente"** (vista pública), **Locación**, **Equipo necesario**, **Personas y personajes**, **Notas para el cliente**, más la **URL publicada** (con vista previa) y **Marcar publicada**.
- [ ] **Enlace para el cliente.** En la pestaña **Producción**, un bloque violeta muestra la **URL pública** de la pieza con botones **Copiar** (cambia a "¡Copiado!") y **Abrir** (nueva pestaña). Solo aparece con una pieza seleccionada.
- [ ] **Botón Compartir (cabecera).** En la cabecera del Composer, entre **Eliminar** y **Guardar**, un botón **Compartir** copia la URL pública al portapapeles (cambia a "¡Copiado!" ~1,5 s). Solo aparece con una pieza seleccionada.
- [ ] **Idea + seguidor independientes.** En **Datos básicos** se eligen la **idea ganadora** (el formato) y el
      **seguidor ideal** (a quién va dirigida) por separado. Al elegir **seguidor**, el panel de **contexto en vivo**
      (derecha) muestra sus **preguntas**, **mitos/verdades** y **dolores/deseos** (ya **no** dependen de la idea).
- [ ] **Reels de referencia.** Si la idea ganadora elegida tiene ejemplos reales (URLs), el panel de contexto los
      lista como **"Referencia 1", "Referencia 2", …** (enlaces que abren en pestaña nueva). Sin ejemplos, no aparece.
- [ ] **Periodo (mover pieza).** En **Datos básicos**, un selector **Periodo** (opción **Sin periodo** + los periodos
      de la marca) permite **reasignar** la pieza a otro periodo, o **rescatar** una pieza sin periodo. Combinado con el
      modo **"Sin periodo"** del selector de cabecera: entra en ese modo, abre la pieza sin asignar y elígele un periodo;
      al hacerlo (autoguardado) desaparece de la lista de "sin periodo" y pasa al periodo elegido.
- [ ] **Autoguardado.** Escribir en cualquier campo (de cualquier pestaña) → se guarda solo (recargar confirma). Indicador "Guardado"; botón **Guardar** guarda todo de una.
- [ ] **Publicación.** Pegar URL → botón **"Vista previa"** muestra la miniatura. Botón **"Marcar publicada"** pone estado Publicada + fecha; luego desaparece.
- [ ] **Evaluación RUM.** En la pestaña RUM, 5 selectores; al elegirlos, el **badge RUM** se actualiza en vivo (rojo ≤5 / amarillo 5–7 / verde >7) y se autoguarda.
- [ ] **Deep-link.** Entrar con `?piece={id}` (o desde el kanban) preselecciona esa pieza.

### Asistente (chat de refinamiento del guión)

> Pestaña **Asistente** (solo aparece si el asistente de IA está configurado — ver [09-ia.md](09-ia.md)).
> Es una **conversación** para pulir el guión de la pieza: el creador pide ajustes y la IA propone versiones
> **sin tocar la pieza**; solo se aplican al elegirlas. El hilo se guarda **por pieza** (persiste al recargar).

- [ ] **Enviar instrucción.** Escribe un ajuste (p. ej. "hazlo más corto") y pulsa **Enviar** (o **⌘/Ctrl + Enter**).
      Aparece tu mensaje (burbuja violeta a la derecha) y un indicador **"Pensando…"** mientras la IA trabaja en 2º plano.
- [ ] **Respuesta de la IA.** Al terminar, aparece una burbuja del **Asistente** con una **nota** de qué cambió y una
      **versión propuesta** del guión (Gancho / Historia / Moraleja / CTA), con botón **Usar esta versión**.
- [ ] **Atajos rápidos.** Los chips (**Más corto**, **Más cálido**, **Más directo**, **Otro gancho**, **Más emocional**)
      envían esa instrucción de un clic.
- [ ] **Iterar mantiene contexto.** Un segundo ajuste ("ahora más cálido") parte de la **última versión** propuesta,
      no del guión original: la conversación acumula contexto (no hay que re-explicar todo).
- [ ] **Aplicar (IA = sugerencia).** Solo al pulsar **Usar esta versión** se reescriben Gancho/Historia/Moraleja/CTA
      en la pestaña **Guión** (autoguardado). Sin pulsar, el guión de la pieza no cambia.
- [ ] **Contador y persistencia.** La pestaña muestra un contador con el nº de mensajes; al recargar o volver a la pieza,
      el hilo sigue ahí. Cambiar de pieza muestra **su** propio hilo (no se mezclan).
- [ ] **Reiniciar.** El botón **Reiniciar** (con confirmación) borra el historial del chat de esa pieza (no afecta al
      guión ya aplicado).
- [ ] **Sin clave de IA.** Si no hay `ANTHROPIC_API_KEY`, la pestaña **Asistente** no aparece.

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
- [ ] **Respuesta del cliente (opcional, tras flag).** **Desactivada por defecto** (`STUDIO_CLIENT_REVIEW`). Con el
      flag activo, al final aparece la sección **"¿Qué te parece?"** con **✅ Aprobar** y **✏️ Pedir cambios** (con
      caja de texto; "Pedir cambios" **exige** nota). Desactivada, la sección **no** aparece y el POST de revisión da 404.
- [ ] **Visible en el Estudio.** En el **Composer**, la pieza muestra la respuesta del cliente: **badge** en la lista
      (Aprobada/Cambios pedidos) y un **banner** en el editor con la nota y cuándo respondió.
- [ ] **Tablero de marca.** En `/m/{token}`, cada tarjeta marca **✅ Aprobada** o **✏️ Cambios pedidos** si el cliente respondió.
- [ ] **Aviso al equipo.** Al responder el cliente, se envía un **correo** a los miembros de la marca (asunto con
      "Aprobada"/"Cambios solicitados", el comentario si lo hay, y botón **"Abrir en el Estudio"**). Va **en cola**:
      requiere `queue:work` y `MAIL_*` configurado (con `MAIL_MAILER=log` en dev, se ve en `storage/logs/laravel.log`).

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

## Uso de IA (`/studio/{marca}/uso-ia`)

> Menú **Análisis → Uso de IA**. Volumen de generaciones de IA por marca (no coste/tokens: cada generación es una
> llamada de pago a Anthropic; esto dimensiona el gasto).

- [ ] **Totales.** Tarjetas con generaciones **totales**, **este mes** y **fallidas**.
- [ ] **Por tipo y por miembro.** Conteos por tipo (Guiones / Ideas / Kickstart) y por usuario de la marca.
- [ ] **Actividad reciente.** Lista de las últimas generaciones con estado (Hecha/Fallida/En curso), tipo, autor y cuándo.
- [ ] **Aislamiento.** Solo cuenta las generaciones de la marca activa.

## Atajos / Búsqueda rápida (⌘K)

- [ ] **Abrir.** El botón **⌘K** de la cabecera (o pulsar **⌘K / Ctrl+K**) abre la paleta de comandos.
- [ ] **Buscar.** Escribir filtra **piezas** e **ideas** de la marca por título (clic → salta a ellas) y los **atajos**
      a las secciones (Generador de ideas/piezas, Composer, Kanban, Periodos, etc.).
