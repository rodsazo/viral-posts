# Hoja de ruta y plan de escalado

> Documento **vivo**. Recoge cómo es probable que escale el sistema y qué secciones nuevas
> aparecerán a corto plazo una vez que se use de verdad para **estrategia y planificación**.
> Para los pendientes puramente de infraestructura/despliegue, ver [../PRODUCCION.md](../PRODUCCION.md).

Leyenda de prioridad: 🔴 Alta · 🟡 Media · 🟢 Baja/Futuro.

---

## La idea que lo predice todo: el bucle abierto

Hoy el sistema es un **motor de planificación y generación** excelente:

```
Investigar audiencia → Idear → Guionizar → CTA → Planificar (Kanban)
```

Pero el contenido sale del sistema y **no vuelve nada**: no hay registro de qué obtuvo vistas, qué
gancho convirtió, ni qué capturó cada CTA de palabra clave. **Casi toda mejora futura es el sistema
intentando cerrar ese bucle.** Por eso las dos primeras piezas de esta hoja de ruta son el
**Calendario** (planificar en el tiempo, no solo por estado) y el **Rendimiento** (medir y aprender).

Ciclo de vida completo al que aspiramos (✅ construido · ⏳ pendiente):

| Fase | Estado | Dónde vive hoy |
|---|---|---|
| 1 · Investigar audiencia | ✅ | Estudio → Kickstart, Audiencia |
| 2 · Idear | ✅ | Estudio → Ideas |
| 3 · Guionizar | ✅ | Estudio → Generador, Composer |
| 4 · Planificar | 🟡 parcial | Kanban (por estado) → **falta Calendario (por fecha)** |
| 5 · Producir | ⏳ | — (media/grabación) |
| 6 · Publicar | ✅ | estado `publicada` + `published_at` |
| 7 · **Medir** | ⏳ | — (**el eslabón que falta**) |
| 8 · **Aprender** | ⏳ | — (ganadores → vuelve a Audiencia/Ideas) |

---

## Escalado: dónde aprieta primero

### 1. 🔴 Coste de IA (el techo real)
Cada generación es una llamada de pago a Anthropic y **no hay presupuestos ni límites por marca**.
Con un usuario son céntimos; con varias marcas y equipos pulsando "Generar 5 guiones" todo el día,
el coste es el límite que se alcanza antes que cualquier problema de infraestructura.
- **Ya tenemos la materia prima:** el canal de log diario de IA (`storage/logs/ai-*.log`, ver
  [../IA.md](../IA.md)) registra prompt + resultado de cada llamada. Es la semilla de un
  **panel de uso y coste de IA** y de **presupuestos por marca**.

### 2. 🟡 Infraestructura (por orden de aparición)
- **BD:** SQLite sirve en dev; producción multi-marca necesita Postgres/MySQL. El escopado por
  `account_id` ya está limpio, así que es un cambio de motor, no una reescritura.
- **Cola:** un solo worker `database` se satura en cuanto dos marcas generan a la vez → pool de
  workers, probablemente Redis, y estado de cola visible por marca.
- **Media:** los logos de marca viven en el disco público. El día que las piezas lleven vídeo/miniatura
  reales hace falta almacenamiento tipo S3. Aún no existe.

### 3. 🟡 Volumen y ciclo de vida del dato
`ContentPiece`, `WinningIdea` y la audiencia (`Question`/`Belief`/`Pain`) crecen **rápido** (es el
objetivo). Harán falta: archivado, dimensión temporal, paginación y deduplicación. Los catálogos
globales (Heras, Ganchos, Referentes, Nichos) escalan bien; la presión está en el dato **por marca**.

---

## Secciones nuevas previstas, por orden de dolor

1. 🔴 **Calendario editorial / cadencia.** El Kanban organiza por *estado*, no por *tiempo*; "Inicio"
   ya muestra *huecos*. → **Spec: [01-calendario.md](01-calendario.md)**.
2. 🔴 **Rendimiento (cierra el bucle).** Capturar resultados reales por pieza publicada y rankear
   ganchos/ideas/CTAs/conciencia por resultado. Convierte el **RUM** (predicción) en algo
   contrastable. → **Spec: [02-rendimiento.md](02-rendimiento.md)**.
3. 🟡 **Panel de uso y coste de IA + presupuestos por marca**, sobre el log de IA ya existente. Junto a
   **voz de marca por cuenta** en el prompt (hoy `config/ai.php` es global; ya guardamos
   `brand_promise`/`main_offers`/`ideal_customer_profile`, falta "tono y voz").
4. 🟡 **Embudo de CTA de palabra clave.** Las Keyword CTAs recién añadidas implican: **lead magnets**,
   mapa palabra→recurso y captura de leads/DMs.
5. 🟡 **Campañas / series / pilares de contenido.** Capa organizativa por encima de `ContentPiece`.
6. 🟢 **Dimensión de plataforma / repurposing.** Una idea → varios formatos/plataformas (TikTok/IG/YT/X).
7. 🟢 **Etapa de producción + flujo de aprobación.** Los roles Admin/Editor ya existen para aprobaciones.
8. 🟢 **Higiene de audiencia e Inbox inteligente.** Tags/archivo/dedup de la audiencia, triaje IA del
   Inbox y un **swipe file** por marca.

---

## Qué está especificado en esta carpeta

| Archivo | Contenido | Prioridad |
|---|---|---|
| [01-calendario.md](01-calendario.md) | Calendario editorial: programar piezas por fecha, cadencia, huecos | 🔴 |
| [02-rendimiento.md](02-rendimiento.md) | Seguimiento de rendimiento real + atribución a ganchos/ideas/CTAs | 🔴 |

Ambas respetan las convenciones del proyecto (ver [../../CLAUDE.md](../../CLAUDE.md)): escopado por
`account_id`, migraciones **aditivas** (con backup previo de la BD), QA en [`/qa/`](../../qa/README.md),
tests en `tests/Feature`, `php artisan test` en verde y `./vendor/bin/pint`.

> **Orden recomendado de construcción:** primero el Calendario (desbloquea la planificación temporal y
> es autocontenido), luego Rendimiento (cierra el bucle y depende de poder atribuir resultados a la
> provenance de cada pieza — ver §"Atribución" en su spec).
