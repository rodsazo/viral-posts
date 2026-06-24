# 02 — Audiencia (Seguidores, Categorías, Preguntas, Dolores)

> La audiencia se edita en el **admin** (este archivo) **y** en el Estudio (hub *Audiencia*, ver [08-estudio.md](08-estudio.md)).

## Datos de la marca

- [ ] **Admin → Datos de la marca:** existen y se guardan **Promesa de la marca**, **Oferta(s) principal(es)** y
      **Perfil del cliente ideal** (textos largos). También editables por el super admin en *Plataforma → Marcas*.
- [ ] **Logo de la marca.** Subir una imagen en *Datos de la marca* (o *Plataforma → Marcas*). Se muestra como
      miniatura en la barra del Estudio y el selector de marca, y en el selector de marca del admin. Sin logo,
      aparece un cuadro con la inicial. (Requiere `php artisan storage:link`.)

## Seguidores ideales

- [ ] **Crear** un seguidor ideal con nombre y descripción. → Aparece en el listado.
- [ ] **Nivel de conciencia.** Campo *Nivel de conciencia (Heras)* (0–4) en el form del seguidor; se guarda.
- [ ] **Editar / Eliminar** (como admin). → Cambios reflejados.
- [ ] **Estado vacío.** Sin seguidores, el listado muestra el mensaje guía ("Define a quién le hablas…").
- [ ] **Relation manager.** Al **editar** un seguidor, pestaña con sus **preguntas**: crear una pregunta desde ahí (hereda la marca). → Se crea asociada al seguidor.
- [ ] **Filtro** "con/sin preguntas".

## Categorías de preguntas

- [ ] **Crear** categoría con nombre y color. → El color se ve como badge.
- [ ] **Editar / Eliminar** (admin).
- [ ] La categoría aparece disponible al crear/editar preguntas.

## Preguntas

- [ ] **Crear** una pregunta: elegir seguidor ideal (obligatorio), categoría (opcional), texto. → Se guarda.
- [ ] **Panel de contexto.** Al elegir el seguidor ideal, a la derecha aparece su descripción y las **preguntas ya existentes** de ese seguidor (evita duplicados).
- [ ] **Filtros (B4).** Filtrar la tabla por **seguidor ideal** y por **categoría**.
- [ ] **Búsqueda** por texto de la pregunta.
- [ ] **Sin vínculo a creencias.** El form de pregunta **ya no** tiene selector de mitos/verdades (las creencias cuelgan del seguidor, no de la pregunta).

## Dolores / Problemas / Deseos (Audiencia → Dolores)

- [ ] **Crear** un dolor: **seguidor ideal (obligatorio)**, **tipo** (Dolor/Problema/Deseo) y enunciado. → Se guarda.
- [ ] **Filtros** por tipo y por seguidor; búsqueda por texto.
- [ ] **Borrar el seguidor** elimina también sus dolores (cascada).

## Creencias por seguidor

- [ ] En el form de **Creencia**, el campo **Seguidor ideal** es **obligatorio**: el mito/verdad cuelga
      directamente del seguidor. Ya **no** se relaciona con preguntas.
- [ ] **Borrar el seguidor** elimina también sus creencias (cascada).

## Preguntas en lote (Audiencia → Preguntas en lote)

- [ ] **Un seguidor, varios lotes.** Elegir seguidor arriba; en un lote pegar **varias líneas** (una pregunta por línea) y una categoría. Añadir un **segundo lote** con otra categoría. Guardar. → Se crean todas las preguntas con su categoría; las líneas vacías se ignoran.
- [ ] **Crear categoría inline** dentro de un lote. → Disponible al instante.
- [ ] Tras guardar, el seguidor queda recordado para seguir cargando.
