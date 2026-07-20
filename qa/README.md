# QA — Guía de pruebas manuales

Suite de pruebas **manuales** para verificar que el sistema funciona de punta a punta. Complementa
los tests automáticos (`php artisan test`), no los sustituye: aquí se prueba lo que un humano ve y hace.

## ⚠️ Regla de mantenimiento

> **Cada vez que se añade o modifica una funcionalidad, hay que actualizar también esta carpeta `/qa/`.**
> Si tocas un flujo, revisa el archivo correspondiente y ajusta/añade los casos de prueba.

## Cómo usar esta suite

1. Prepara el entorno (abajo).
2. Recorre cada archivo por área; cada caso tiene **Precondición → Pasos → Resultado esperado**.
3. Marca `[x]` lo que pasa; anota lo que falla con capturas y la URL.

## Áreas

| Archivo | Cubre |
|---|---|
| [01-acceso-y-marcas.md](01-acceso-y-marcas.md) | Login, multi-marca (tenancy), aislamiento, cambio de marca |
| [02-audiencia.md](02-audiencia.md) | Seguidores ideales, categorías, preguntas (+ en lote) |
| [03-conocimiento.md](03-conocimiento.md) | Creencias (+ en lote), ideas ganadoras (+ en lote), visibilidad multi-salto |
| [04-produccion.md](04-produccion.md) | Piezas de contenido, guión, publicar, kanban (Filament) |
| [05-referencia.md](05-referencia.md) | Plantillas Heras, referentes virales, nichos, vista previa |
| [06-equipo-y-permisos.md](06-equipo-y-permisos.md) | Invitaciones, miembros, roles Admin/Editor, permisos |
| [07-busqueda-dashboard.md](07-busqueda-dashboard.md) | Búsqueda global, filtros, dashboard, detección de huecos |
| [08-estudio.md](08-estudio.md) | Frontend "Estudio": Inicio, Inbox, Kanban, Composer, selector de marca |
| [09-ia.md](09-ia.md) | Asistente de IA (Claude): guión asistido en admin y Estudio (sugerencia, no reemplazo) |
| [10-marca.md](10-marca.md) | Diseño de Marca (Estudio): logo e identidad (nombre, descripción, promesa, ofertas, cliente ideal) |
| [11-personajes.md](11-personajes.md) | Personajes de Marca (Estudio): CRUD, generador con IA, chat de refinamiento e inyección en la generación |

## Entorno de pruebas

- **Arrancar**: `php artisan serve` (requiere Node ≥ 22.12 y `npm run build` para los estilos del Estudio).
- **Panel admin**: http://127.0.0.1:8000/admin
- **Usuarios demo** (seeder):
  | Email | Contraseña | Rol en "El Rod y El Rol" |
  |---|---|---|
  | `rodsazo@gmail.com` | `password` | Administrador |
  | `editor@elrodyelrol.test` | `password` | Editor |
- **Marca demo**: *El Rod y El Rol* (con datos de ejemplo).
- **Correos** (invitaciones): en dev el mailer es `log` → revisa **`storage/logs/laravel.log`** para ver el email y su enlace de aceptación. (También puedes copiar el enlace desde la tabla de Invitaciones.)
- **Estudio**: http://127.0.0.1:8000/admin → botón **"Abrir Estudio"** en la barra superior (izquierda del buscador), o `/studio/el-rod-y-el-rol`.

## Notas / comportamientos por diseño (no son bugs)

- En el menú hay **dos "Ideas ganadoras"**: una en *Producción* (tus ideas) y otra en *Referencia* (catálogo Heras). Es intencional.
- La **contraseña `password`** y los datos demo son solo de desarrollo (ver `docs/PRODUCCION.md`).
- Las invitaciones **caducan a los 7 días**.
- El campo **Formato** y los enums (Estado, Objetivo, etc.) son listas fijas (no editables desde el panel).
- La **vista previa de posts** de Instagram suele no obtenerse automáticamente (su API lo bloquea); se pega la URL de imagen a mano. TikTok y muchas webs sí funcionan.
- Al obtener la vista previa **se guarda una copia propia** de la miniatura (reducida) en el disco de marca (`reference-images/`, S3 en producción), no la URL de la red social — esas caducan y dejaban la imagen rota. Para arreglar una imagen ya rota, vuelve a pulsar **"Vista previa"** (re-captura desde el enlace del post).
- El **RUM** usa una escala fija por factor (**1 / 1.3 / 1.5848**); su rango va de 1.0 a ~10.0. Color: rojo ≤5, amarillo 5–7, verde >7.
