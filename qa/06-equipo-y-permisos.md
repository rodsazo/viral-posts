# 06 — Equipo, invitaciones y permisos

> Solo los **administradores** ven el grupo de menú **Equipo** (Miembros, Invitaciones).

## Invitaciones (Equipo → Invitaciones)

- [ ] **Enviar invitación.** Como admin, crear invitación con email + rol. → Aparece en la tabla con estado **Pendiente**; el correo se escribe en `storage/logs/laravel.log` con el enlace.
- [ ] **Copiar enlace** desde la columna "Enlace".
- [ ] **Email duplicado.** Invitar un email que ya tiene invitación → **mensaje de validación** ("Ya existe una invitación…"), NO un error 500.
- [ ] **Miembro existente.** Invitar a alguien que ya es miembro → validación lo impide.

### Aceptación — usuario nuevo
- [ ] Abrir el enlace (ventana privada). → Pantalla para **crear cuenta** (nombre + contraseña).
- [ ] Completar → se crea la cuenta, inicia sesión y entra a la marca con el rol asignado.

### Aceptación — usuario existente
- [ ] Abrir el enlace **sin sesión** (con una cuenta ya existente para ese email). → Redirige al **login**.
- [ ] Iniciar sesión con ese email. → **Vuelve automáticamente** al enlace y muestra "Aceptar invitación". Aceptar → entra a la marca.
- [ ] Abrir el enlace con **otra** sesión abierta (email distinto). → Mensaje "esta invitación es para X, cierra sesión…".

### Caducidad
- [ ] Una invitación **caducada** (>7 días) → al abrir el enlace muestra "ha caducado"; no se puede aceptar.
- [ ] **Reenviar** una invitación → renueva la caducidad (+7 días) y reescribe el correo. La tabla la muestra como Pendiente.

### Revocar
- [ ] **Revocar** (eliminar) una invitación pendiente. → Desaparece; su enlace deja de funcionar (404).

## Miembros (Equipo → Miembros)

- [ ] Listado de miembros con su **rol** (Admin/Editor).
- [ ] **Cambiar rol** (Admin↔Editor). → Se actualiza.
- [ ] **Quitar** un miembro. → Sale de la marca.
- [ ] **Guardas**: no puedes **quitarte a ti mismo**; no puedes dejar la marca sin el **último administrador** (cambiar rol o quitar al último admin → aviso de error).

## Permisos por rol (login como `editor@elrodyelrol.test`)

- [ ] El editor **no ve** el grupo **Equipo** (ni Miembros ni Invitaciones).
- [ ] El editor **puede crear/editar** contenido (preguntas, ideas, piezas, etc.).
- [ ] El editor **no ve botones de eliminar** en ningún listado (ni borrado masivo).
- [ ] El admin sí puede eliminar.
