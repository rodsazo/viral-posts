# 06 — Equipo, invitaciones y permisos

> Hay **dos niveles** de rol: **por marca** (Admin/Editor, grupo *Equipo*) y de **plataforma**
> (super admin, grupo *Plataforma*). El super admin se gestiona **solo por consola** (ver `docs/USUARIOS.md`).
> Solo los **administradores** de la marca ven el grupo de menú **Equipo** (Miembros, Invitaciones).

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

## Plataforma — Super admin (solo por consola)

> Crear/otorgar/revocar super admin: `php artisan super-admin grant|revoke|list <email>`. Ver `docs/USUARIOS.md`.

- [ ] **Sin super admin.** Un usuario normal **no ve** el grupo **Plataforma** (Marcas, Usuarios). Visitar
      `/admin/{marca}/accounts` o `/admin/{marca}/users` → **403**.
- [ ] **Catálogos globales (Heras/Referentes/Nichos).** Un usuario normal **los ve** pero **no** tiene botones
      de crear/editar/borrar; entrar a `…/heras-templates/create` → **403**.
- [ ] **Como super admin** (`php artisan super-admin grant <tu-email>`):
  - [ ] Aparece el grupo **Plataforma** con **Marcas** y **Usuarios**.
  - [ ] **Marcas:** listar todas, crear una (el slug se autogenera), editar; en una marca, pestaña **Miembros**
        para **añadir** un usuario existente con rol, cambiar rol y quitar (con guarda del último admin).
  - [ ] **Suspender marca.** En *Marcas*, acción **Suspender** (y **Reactivar**). Una marca suspendida: sus
        miembros **no la ven** en el selector y reciben **403** al entrar (panel y `/studio/{marca}`); el super
        admin sí puede entrar.
  - [ ] **Usuarios:** directorio de todos los usuarios (con marca(s), badge de activo y de super admin);
        editar nombre/email. El toggle *Super admin* está **deshabilitado** (se gestiona por consola). No hay crear ni borrar.
  - [ ] **Desactivar usuario.** Acción **Desactivar** (y **Activar**). Un usuario desactivado recibe **403**
        en el panel y en el Estudio. **No** aparece la acción para desactivarte **a ti mismo**.
  - [ ] Puede crear/editar/borrar en los **catálogos globales**.
  - [ ] El super admin ve **todas las marcas** en el selector de marca, aunque no sea miembro.
- [ ] **Auto-registro desactivado.** Ya **no** existe la opción de "registrar nueva marca" para usuarios; las
      marcas las crea el super admin.
