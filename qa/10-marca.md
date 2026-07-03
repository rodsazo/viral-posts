# 10 · Marca (Estudio)

> Edición de la identidad de la marca activa desde el Estudio. Menú **Marca → Diseño de Marca**
> (`/studio/{marca}/marca/diseno`). Pensado para crecer: aquí se irán añadiendo más campos de marca.

## Navegación

- [ ] **Menú "Marca".** En la barra superior del Estudio, **"Marca"** es el **primer** grupo del menú (icono ✨).
      Su desplegable tiene **Diseño de Marca** (icono de muestrario). El grupo se resalta cuando estás en esa pantalla.

## Diseño de Marca

- [ ] **Carga.** La pantalla abre con los **valores actuales** de la marca activa: logo, nombre, descripción,
      promesa, oferta(s) y perfil del cliente ideal.
- [ ] **Logo.** Muestra el logo actual (o un marcador si no hay). **Subir/Cambiar** abre el selector de archivo
      (PNG/JPG ≤ 4 MB); al elegir, se sube (indicador "Subiendo…") y la vista previa se actualiza. **Quitar**
      (con confirmación) elimina el logo. El archivo se guarda en el disco de marcas (`brand-logos`), igual que en el admin.
- [ ] **Validación de imagen.** Subir un archivo que no sea imagen (o > 4 MB) muestra un error y no cambia el logo.
- [ ] **Autoguardado.** Editar cualquier campo de texto y salir del campo (blur) **guarda solo** (aparece el
      badge **Guardado**). El botón **Guardar** guarda todo de una.
- [ ] **Promesa/Ofertas alimentan la IA.** Cambiar **Promesa de la marca** u **Oferta(s) principal(es)** se refleja
      en la siguiente generación de ideas/guiones (esos campos se envían a la IA en todo el contenido).
- [ ] **Persistencia.** Recargar la pantalla mantiene los cambios. El mismo cambio se ve también en el admin
      (Filament → Marcas) y viceversa: es la misma marca.
- [ ] **Coherencia multi-marca.** Cambiar de marca (selector arriba a la derecha) y volver muestra los datos de
      **cada** marca por separado; editar una no afecta a la otra.
- [ ] **Nombre.** Cambiar el **nombre** actualiza el rótulo de la marca en la cabecera; **no** cambia el `slug`
      (la URL `/studio/{slug}` sigue igual, no se rompen enlaces).

## Acceso

- [ ] **Solo miembros.** Un usuario que **no** es miembro de la marca (ni super admin) recibe **403** al entrar a
      `/studio/{marca}/marca/diseno`.
