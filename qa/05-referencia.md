# 05 — Referencia (Plantillas Heras, Ganchos, Referentes virales, Nichos)

> Estos catálogos son **globales** (compartidos por todas las marcas), no por marca, y se editan **solo en el admin**.
> Crear/editar/borrar está reservado al **super admin** (cualquier miembro del admin puede verlos).

## Nichos

- [ ] **Crear** un nicho: nombre, descripción, color. → Badge de color.
- [ ] Listado con conteo de **referentes** por nicho.

## Referentes virales

- [ ] **Crear** un referente: nombre, **nicho** (relación, con creación inline), URL de Instagram, notas.
- [ ] Listado con badge de nicho, conteo de **plantillas** y enlace 🔗 al Instagram.
- [ ] **Filtro** por nicho.

## Plantillas Heras (menú: Referencia → "Ideas ganadoras")

- [ ] Hay **30 plantillas** sembradas, asignadas al referente **Víctor Heras**. *(Ya **no** tienen "número".)*
- [ ] **Editar** una plantilla: nombre, formato sugerido, mecanismo, estructura, **referente viral**, **URL del post** y **URL de imagen**.
- [ ] **Vista previa.** En el campo URL del post, botón **"Obtener vista previa"** → intenta traer la miniatura (TikTok/og:image). Si falla (Instagram), pegar la URL de imagen a mano.
- [ ] **Más URLs de referencia.** Repeater **"Añadir URL"** para sumar **varios** posts adicionales (además del principal con vista previa), igual que en las Ideas ganadoras.
- [ ] **Tabla** (orden por nombre): miniatura, nombre, referente, nicho, mecanismo, nº de ideas que la usan. *(Sin columna de número.)*
- [ ] **Filtros**: por **referente viral** y por **nicho**.
- [ ] **Vista (👁)** de una plantilla: imagen grande + enlace al post + **lista de URLs de referencia adicionales**.
- [ ] **Relación con Idea.** En una Idea ganadora, el selector "Plantilla Heras" lista las plantillas por **nombre**; queda visible en la tabla e infolist de la idea.
- [ ] **Búsqueda global** encuentra plantillas/referentes y muestra su referente/nicho como subtítulo.

## Plantillas de gancho (menú: Referencia → "Ganchos (plantillas)")

> Catálogo **global de referencia** (compartido por todas las marcas), solo super admin. Plantillas para el
> **gancho** (primera parte de una pieza). Los ganchos **propios de cada marca** se gestionan en el Estudio
> (**Contenido → Ganchos**, ver [08-estudio.md](08-estudio.md)) y **no** aparecen en este listado del admin.

- [ ] **Crear** una plantilla de gancho: **Nombre**, **Referente** (obligatorio), **Ícono** (FontAwesome), **Objetivo**, **Notas**.
- [ ] **Ejemplos por nicho:** genérico + Salud + Sexo + Dinero + Desarrollo Personal (textos).
- [ ] **Referencias:** **URL de referencia** + **Ejemplos reales** (lista de URLs; añadir/quitar varias).
- [ ] **Picker de ícono.** El campo *Ícono* es un desplegable **buscable** con íconos de FontAwesome; al guardar,
      el ícono aparece en la tabla. *(Requiere cargar FontAwesome 7 Pro: define `FONTAWESOME_URL` en `.env` con el
      Kit `.js` o la CSS `.css`. Sin esa URL, el picker funciona por nombre pero los íconos no se dibujan.)*
- [ ] **Permisos.** Un usuario que **no** es super admin ve la lista pero entrar a *crear* da **403**.
