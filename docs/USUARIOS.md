# Usuarios, marcas y roles

Modelo de permisos en dos niveles.

## 1. Rol por marca (tenant) — `TeamRole`

Pivote `account_user.role`: **Admin** o **Editor**. Es el rol del día a día dentro de una marca:

- **Admin:** gestiona miembros e invitaciones de *su* marca (grupo *Equipo*), y puede borrar contenido.
- **Editor:** crea/edita contenido; no ve *Equipo* ni botones de borrado.

Se asigna por invitación (grupo *Equipo → Invitaciones*) o desde *Plataforma → Marcas → Miembros* (super admin).

## 2. Rol de plataforma — Super admin

Concepto **ortogonal** al rol por marca: columna `users.is_super_admin`. Concede **acceso total** vía
`Gate::before` (en `AppServiceProvider`). Gobierna lo que es transversal a todas las marcas:

- **Catálogos globales** (Heras / Referentes / Nichos): cualquier miembro **los ve** (se referencian al crear
  ideas/piezas y en el generador), pero **crear/editar/borrar** queda reservado al super admin
  (`App\Filament\Concerns\RestrictsMutationToSuperAdmins`).
- **Marcas** (grupo *Plataforma → Marcas*): listar/crear/editar todas las marcas y gestionar sus **miembros**
  (añadir usuario existente con rol, cambiar rol, quitar — con guarda del último admin). **Suspender**
  (`accounts.is_active`): una marca suspendida desaparece del selector de sus miembros y les bloquea el acceso
  (panel y Estudio); el super admin sí puede entrar (para reactivarla o arreglarla).
- **Usuarios** (grupo *Plataforma → Usuarios*): directorio global; editar nombre/email. Sin crear/borrar desde la UI.
  **Desactivar** (`users.is_active`): un usuario desactivado no puede acceder al panel ni al Estudio. No puedes
  desactivarte a ti mismo (evita bloquearte). Reactivable desde la misma pantalla.

El super admin ve **todas** las marcas en el selector (aunque no sea miembro de ninguna): `User::getTenants()`
y `canAccessTenant()` lo contemplan.

### Gestión SOLO por línea de comando

Crear un super admin, u otorgar/revocar el rol a otro usuario, **solo** se hace por consola (no hay UI; en
el form de Usuarios el toggle está deshabilitado, y el campo no es `fillable`):

```sh
php artisan super-admin grant correo@ejemplo.com    # otorga (si el usuario no existe, ofrece crearlo)
php artisan super-admin revoke correo@ejemplo.com   # revoca
php artisan super-admin list                         # lista los super admins
```

## Creación de marcas

El **auto-registro abierto** de marcas está **desactivado** (se quitó `tenantRegistration` del panel). Las
marcas las crea el **super admin** desde *Plataforma → Marcas*. El `slug` se autogenera del nombre si se deja vacío.

## Bootstrap (primer super admin)

En un entorno nuevo (o en dev), designa tu super admin:

```sh
php artisan super-admin grant tu-correo@ejemplo.com
```

### Primer admin real en producción

En producción **no** corre el seeder demo (no hay usuarios con contraseña fija). El admin real se crea con un
comando guiado y seguro (contraseña elegida en el momento; opcionalmente crea su primera marca y/o super admin):

```sh
php artisan app:create-admin
# o no-interactivo parcial:
php artisan app:create-admin --email=tu-correo@ejemplo.com --name="Tu Nombre"
```

Ver el flujo completo de despliegue en [PRODUCCION.md](PRODUCCION.md) (punto #0).
