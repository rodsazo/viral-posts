# 04 — Producción (Piezas de contenido, Kanban)

## Pieza de contenido (formulario)

- [ ] **Crear** una pieza. La idea ganadora es **opcional** (puede ser "pieza suelta").
- [ ] **Cascada multi-salto.** Al elegir una idea o un **seguidor**, el panel muestra las preguntas (vía idea) y los **mitos/verdades del seguidor** en vivo.
- [ ] **Guión** en 4 campos: gancho, historia, moraleja, CTA. Recordatorio de creencias dentro del guión.
- [ ] **Producción.** Sección con **Locación**, **Equipo necesario** y **Personas y personajes**.
- [ ] **Meta**: objetivo (Viralidad/Venta), formato, estado, calificación.
- [ ] **Crear idea inline** desde el selector de idea.
- [ ] **Publicación.** Rellenar URL; botón **vista previa** intenta traer la miniatura del post. Fecha de publicación.

## Evaluación RUM (Relevancia Única de Mercado)

- [ ] En el form de pieza, sección **Evaluación RUM** con 5 selectores (amplitud, intensidad, universalidad, inmediatez, independencia), cada uno con 3 opciones.
- [ ] Al elegir los 5, el **RUM se calcula en vivo** (producto redondeado a 1 decimal). Con todo al máximo → **10.0**; todo al mínimo → **1.0**.
- [ ] **Color** del badge: **rojo** si RUM ≤ 5, **amarillo** si 5–7, **verde** si > 7.
- [ ] Si falta algún factor → "Sin evaluar".
- [ ] Al guardar, el RUM persiste; la **tabla de piezas** muestra la columna **RUM** (badge de color, **ordenable**) y un **filtro** por rango (Alto / Medio / Bajo / Sin evaluar).
- [ ] La **vista (👁)** de la pieza muestra el RUM y el detalle de los 5 factores elegidos.

## Acción rápida "Marcar publicada"

- [ ] En la tabla de piezas, una pieza no publicada muestra el botón **"Marcar publicada"**. Pulsar → confirma → estado pasa a **Publicada** y se estampa la **fecha** de hoy. El botón desaparece.

## Tabla de piezas

- [ ] **Columnas**: estado, objetivo, formato, idea, calificación, enlace (🔗), fecha publicada.
- [ ] **Filtros**: estado, objetivo, formato, calificación, "con idea / sueltas".
- [ ] **Badge de navegación**: el menú "Piezas de contenido" muestra en ámbar el nº de piezas **sin publicar**.
- [ ] **Vista (👁)** de una pieza: muestra toda la info + preguntas (vía idea) y mitos/verdades (del seguidor ideal) (solo lectura).
- [ ] **Permisos.** Como **Editor** (login `editor@elrodyelrol.test`), el botón de **eliminar no aparece**; como **Admin** sí.

## CTAs (Producción → CTAs)

> Llamadas a la acción de la marca (escopadas por marca). También gestionables en el Estudio (📣 CTAs).

- [ ] **Crear/editar** una CTA: **categoría** (Seguir / Palabra clave) + **texto** (máx. 600).
- [ ] **Tabla**: badge de categoría + texto; **filtro** por categoría.
- [ ] **Permisos.** Cualquier miembro puede crear/editar; **eliminar** queda reservado al **Admin** de la marca.

## Kanban (Producción → Kanban, en el panel Filament)

- [ ] **Columnas por estado** (Planificación → Publicada) con tarjetas.
- [ ] **Arrastrar** una tarjeta a otra columna → el estado **se guarda** (recargar lo confirma) y aparece notificación.
- [ ] Cada tarjeta muestra título, objetivo, formato e idea (o "Pieza suelta").
