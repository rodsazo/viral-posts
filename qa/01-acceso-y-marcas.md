# 01 — Acceso y marcas (tenancy)

## Login y acceso

- [ ] **Login válido.** Ir a `/admin`, entrar con `rodsazo@gmail.com` / `password`. → Entra al panel de la marca *El Rod y El Rol*.
- [ ] **Login inválido.** Email o contraseña incorrectos. → Muestra error, no entra.
- [ ] **Logout.** Menú de usuario → cerrar sesión. → Vuelve al login; al intentar `/admin` pide login.
- [ ] **Ruta protegida sin sesión.** Sin loguear, abrir `/studio/el-rod-y-el-rol`. → Redirige a `/admin/login`.

## Crear y cambiar de marca

- [ ] **Crear marca.** Con el selector de marca (arriba) → registrar/crear nueva marca con nombre y descripción. → Se crea y quedas dentro de ella como administrador.
- [ ] **Cambiar de marca.** Si perteneces a ≥2 marcas, usar el selector. → Cambia el contexto; los listados muestran solo datos de la marca activa.
- [ ] **Editar marca.** Perfil de la marca → cambiar nombre/descripción. → Se guarda.

## Aislamiento por marca (multi-tenancy)

- [ ] **Datos aislados.** Crear una pregunta en la marca A. Cambiar a la marca B. → La pregunta de A **no** aparece en B.
- [ ] **No fugas en relaciones.** Al relacionar (p. ej. preguntas de una idea), solo aparecen registros de la marca activa.
- [ ] **Acceso ajeno bloqueado.** Con un usuario que **no** pertenece a una marca, intentar abrir su URL `/admin/{slug}` o `/studio/{slug}`. → 403 / redirección; no ve datos.
