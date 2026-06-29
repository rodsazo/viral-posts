# 03 — Conocimiento (Creencias, Ideas ganadoras, multi-salto)

> **El Seguidor Ideal es el centro.** Las creencias (mitos/verdades) cuelgan **directamente de un
> seguidor ideal** (obligatorio); ya **no** se relacionan con preguntas.

## Creencias (mitos y verdades)

- [ ] **Crear** una creencia: **Seguidor ideal (obligatorio)**, tipo (Mito/Verdad), afirmación, postura. → Badge de color por tipo (Mito rojo, Verdad verde).
- [ ] **Sin seguidor → error.** Guardar sin elegir seguidor no crea nada (campo requerido).
- [ ] **Tabla**: columna **Seguidor ideal** (badge); **filtros** por tipo y por **seguidor ideal**.

## Creencias en lote (Audiencia → Creencias en lote)

- [ ] **Seguidor (obligatorio).** Elegir el seguidor arriba; todas las creencias del lote cuelgan de él.
- [ ] **Dos cajas.** Pegar mitos en la columna roja y verdades en la verde (una por línea). Guardar. → Se crean con su tipo correcto; líneas vacías ignoradas; todas con el seguidor elegido.
- [ ] **Sin seguidor → error.** Guardar sin seguidor avisa y no crea nada.

## Ideas ganadoras

- [ ] **Crear** una idea: título, **estado** (Borrador/Hipótesis/Fija/Descartada; por defecto Borrador), concepto, mecanismo de viralidad (enum), plantilla Heras (opcional).
- [ ] **Estado.** Columna **Estado** (badge) en la tabla y **filtro** por estado. Distinto de la "Validación".
- [ ] **Ejemplos reales (URLs).** Repeater "Añadir ejemplo" con una o varias URLs de posts virales de otros creadores
      (Instagram/TikTok…). Sustituye al antiguo campo único "Referencia viral" (los valores anteriores se migraron
      como primer ejemplo).
- [ ] **Validación derivada.** Una idea **con** al menos un ejemplo aparece como **Validada** (badge verde); **sin**
      ejemplos, **Pendiente de validación** (badge gris). No es editable a mano: depende de los ejemplos.
- [ ] **Seguidor ideal (obligatorio).** La idea se dirige a un seguidor; de él salen sus preguntas y mitos/verdades.
- [ ] **Relacionar preguntas** (N:M) → multiselect **acotado al seguidor** elegido (solo aparecen sus preguntas; deshabilitado hasta elegir seguidor).
- [ ] **Contexto del seguidor (vital).** Al elegir el **seguidor**, el panel derecho muestra **en vivo** sus mitos/verdades. Al elegir preguntas, se listan arriba.
- [ ] **Vista (ojo 👁).** En la vista de la idea: seguidor ideal, badge de validación, lista de ejemplos (🔗) y "Mitos y verdades del seguidor".
- [ ] **Relation manager.** Al editar una idea, pestaña con sus **piezas** (crear/editar).
- [ ] **Crear pregunta inline** desde el selector de preguntas de la idea.
- [ ] **Filtros** por **estado**, mecanismo, "con/sin preguntas" y **Validación** (validadas / pendientes).

## Ideas en lote (Producción → Ideas en lote)

- [ ] **Varias ideas.** Añadir varias filas (título + concepto + mecanismo + preguntas), con filtro de seguidor arriba. Guardar. → Se crean todas con sus vínculos.
- [ ] **Validación.** Una fila sin título o concepto → error, no guarda.
