# 11 · Personajes de Marca (Estudio)

> El **Personaje de Marca** es la "fuente de verdad" sobre quién es la marca frente a cámara
> (arquetipo, enemigo, posturas, historia de origen, voz, identidad visual, conversión y reglas).
> Menú **Marca → Personajes de marca** (CRUD) y **Marca → Generador de personajes** (IA).
> Se construye siguiendo un framework de 9 secciones; contexto en [../docs/IA.md](../docs/IA.md).

## Navegación

- [ ] **Menú "Marca".** Además de *Diseño de Marca*, el desplegable tiene **Personajes de marca** y
      **Generador de personajes**. El grupo "Marca" se resalta en las tres pantallas.

## CRUD (Personajes de marca)

- [ ] **Lista + editor.** A la izquierda, la lista de personajes de la marca (nombre + arquetipo). Al elegir uno,
      a la derecha se abre el editor con las **9 secciones**. **Nuevo** crea uno en blanco.
- [ ] **Autoguardado.** Editar cualquier campo (escalar o de lista) guarda solo (badge **Guardado**). El botón
      **Guardar** guarda todo de una.
- [ ] **Listas repetibles.** Enemigos concretos, posturas (con tipo principal/secundaria + marca de "puente"),
      props (por momento: durante/fondo/cierre), formatos, CTAs y reglas se **añaden y quitan** en filas. Las filas
      vacías se descartan al guardar.
- [ ] **Borrado (solo Admin).** El botón **Eliminar** (con confirmación) solo aparece para administradores de la marca.
- [ ] **Aislamiento por marca.** Cada marca ve solo sus personajes; cambiar de marca muestra los suyos.

## Generador de personajes (IA)

- [ ] **Precarga.** El formulario llega con la **promesa, ofertas y cliente ideal** de la marca ya rellenados, y la
      **audiencia** derivada de los seguidores ideales (editable). No re-pregunta lo que el app ya sabe.
- [ ] **Insumos nuevos.** Pide **destino de conversión + CTAs reales**, si es **top-of-funnel** de otra marca, y los
      **hechos de la historia de origen** (+ el arco: ¿sufrías el problema o lo causabas?). La IA no inventa la historia.
- [ ] **Generar.** Con al menos la **promesa** presente, **Generar personaje** encola la IA (indicador "Construyendo…").
      Al terminar, crea el personaje y **abre el editor** con las 9 secciones completas (mensaje de éxito).
- [ ] **Nombre.** Si diste un **nombre**, se respeta; si lo dejaste vacío, la IA propone uno.
- [ ] **Sin worker de cola.** Como corre en segundo plano, hace falta `php artisan queue:work` (igual que el resto de IA).
- [ ] **Sin clave de IA.** Sin `ANTHROPIC_API_KEY`, la pantalla muestra un aviso y no genera.

## Chat de refinamiento (en el editor)

- [ ] **Panel de chat.** Con un personaje abierto y la IA configurada, a la derecha hay un chat **Refinar con IA**.
- [ ] **Refinar.** Pide un ajuste ("cambia el enemigo", "arquetipo más cercano", "otra firma verbal"); la IA propone
      una **versión revisada** del personaje (nota + botón **Usar esta versión**). Se aplica **solo** al pulsarlo.
- [ ] **Aplicar.** Al usar una versión, el editor se **recarga** con los campos actualizados (el **nombre** no cambia).
- [ ] **Sobre la versión guardada.** El refinamiento parte del documento **guardado** del personaje; aplica y sigue iterando.
- [ ] **Reiniciar.** El botón de reinicio (con confirmación) borra el hilo del chat de ese personaje.

## Inyección en la generación de contenido

- [ ] **Composer.** En **Datos básicos** hay un selector **Personaje de marca** (opcional). Si eliges uno, las
      **sugerencias de guión** y el **chat del guión** (pestaña Asistente) escriben con su voz/posturas/reglas. La pieza
      **recuerda** el personaje.
- [ ] **Generador de piezas.** Un selector opcional de personaje; las piezas creadas quedan asociadas a él.
- [ ] **Generador de ideas.** Un selector opcional de personaje; las ideas salen "en personaje".
- [ ] **Sin personaje.** Si no eliges ninguno (o la marca no tiene personajes), todo funciona igual que antes.
