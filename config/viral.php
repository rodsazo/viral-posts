<?php

/*
|--------------------------------------------------------------------------
| Conocimiento viral: principios rectores + formatos (gestionado en CÓDIGO)
|--------------------------------------------------------------------------
|
| Dos catálogos que el creador puede elegir (opcionalmente) al generar/refinar
| guiones con IA. Cada elección añade un bloque de INSTRUCCIONES ADICIONALES al
| prompt. Si no se elige nada, no se añade nada.
|
| - `principles.guides`: guías de principios rectores VERSIONABLES (p. ej.
|   "Víctor Heras 2026", "Víctor Heras 2025", "Álvaro Guijón 2026"…). Se elige una.
| - `formats`: fórmulas virales con estructura, indexadas por el valor del enum
|   ContentFormat (el "Formato principal" que ya se elige en la pieza). Cada una
|   puede tener SUBFORMATOS (sin versiones). Se editan aquí, sin tocar código.
|
| La etiqueta del formato principal la da ContentFormat::getLabel(); aquí solo va
| su estructura/instrucciones y sus subformatos.
*/

return [

    'principles' => [

        // Clave por defecto sugerida en la UI (null = ninguna preseleccionada).
        'default' => null,

        'guides' => [

            'heras-2026' => [
                'label' => 'Víctor Heras 2026',
                'instructions' => <<<'TXT'
                Aplica estos PRINCIPIOS RECTORES DE VIRALIDAD (metodología Víctor Heras 2026). El guion debe cumplirlos:

                1. La idea manda sobre la edición. Toda idea debe cumplir al menos 2 de 3: tocar un deseo o inseguridad UNIVERSAL, desafiar una creencia común del público, o entenderse en 3 segundos.
                2. Habla de dolores, problemas y deseos GENERALES, INMEDIATOS, TANGIBLES y con los que casi cualquier persona podría identificarse (no de tecnicismos del nicho). Cuanto más amplio y humano el dolor, más alcance.
                3. Súbete a un DEBATE PREEXISTENTE: un tema que la audiencia ya discute con carga emocional (foros, grupos, sobremesas). Si hay que "explicar por qué esto es polémico", el tema no sirve.
                4. Gancho en los primeros 0–3 segundos, sin intro, sin saludo, sin contexto. Debe entenderse solo. Asume que el espectador hace scroll y solo verá el inicio si el gancho no falla.
                5. Retención distribuida: reparte la tensión a lo largo del video (reganchos), no toda al inicio. Retén la respuesta/el método lo más tarde que tenga sentido.
                6. Polariza contra una IDEA o un tercero abstracto (el mito, el elitista), NUNCA contra la audiencia objetivo ni contra el interlocutor. El novato siempre está a salvo.
                7. Incluye UNA analogía cotidiana y potente que reencuadre "lo raro" como obvio, tomada de la vida diaria del público (no del nicho). Debe funcionar dicha en voz alta en una sobremesa; si requiere explicación, descártala. (Es lo que la IA más suele omitir: no la olvides.)
                8. La marca/producto entra SOLO resolviendo una objeción concreta, con features reales y verificables (nunca como anuncio ni con features inventadas). Marca con [HUECO: ...] lo que deba confirmar la persona.
                9. Un SOLO CTA por pieza, coherente con el objetivo. Remata, si encaja, con un contraste irónico que exponga una incoherencia cotidiana ("pagas por X absurdo pero cuestionas Y razonable") y dé permiso emocional para cambiar de opinión.
                10. Congruencia con la marca: el contenido se apoya en lo que la marca ES y VENDE. La info de la IA solo rellena huecos, nunca es la base.
                TXT,
            ],

        ],

    ],

    // Formatos virales, indexados por el VALOR del enum ContentFormat.
    'formats' => [

        'personajes' => [
            'instructions' => <<<'TXT'
            FORMATO «Personajes»: una mini secuencia de teatro con dos o más personajes en pantalla, interpretados por personas distintas o por el mismo creador (cambiando ropa, peinado y ubicación de la toma) que conversan o debaten. Directrices:
            - Cada personaje tiene una voz y postura claras y consistentes; se distinguen a simple vista (vestuario/encuadre).
            - El diálogo avanza por turnos cortos, ritmo de conversación real, sin narrador.
            - Consistencia visual serial entre piezas (mismo fondo, mismo prop) para reconocimiento.
            - La estructura concreta la define el SUBFORMATO elegido.
            TXT,
            'subformats' => [
                'esceptico-convencido' => [
                    'label' => 'Diálogo escéptico ↔ convencido',
                    'instructions' => <<<'TXT'
                    SUBFORMATO «Diálogo escéptico ↔ convencido»: el ESCÉPTICO encarna las dudas y objeciones reales de la audiencia; el CONVENCIDO encarna a la marca/solución. Sigue esta estructura de 6 pasos, en orden:

                    PASO 1 — Pregunta escéptica de apertura (0–2 s, sin intro): el escéptico abre con la duda tal como la diría alguien real, con incredulidad ("¿En serio…?", "Sé honesto, ¿para qué…?"). Es el gancho: entendible sin contexto en 3 s.
                    PASO 2 — Afirmación firme y corta: el convencido responde sin rodeos ni disculpas, planta la bandera ("Sí. Y es totalmente válido.").
                    PASO 3 — Escalera de objeciones (3–5 rondas): cada objeción es consecuencia de la respuesta anterior y ESCALA de lógica → práctica → personal/emocional. No es una lista de FAQs sueltas.
                    PASO 4 — Analogía desbloqueadora (clímax): UNA comparación cotidiana que reencuadra lo raro como obvio.
                    PASO 5 — Última barrera práctica (acceso/precio/riesgo) → el producto aparece resolviéndola con features reales.
                    PASO 6 — Remate de contraste irónico + el ESCÉPTICO toma la acción en primera persona (prueba social escenificada). Un solo CTA.

                    Reglas: cada objeción debe pasar el test "¿una persona real diría esto con estas palabras?" (nada de hombres de paja). El escéptico NUNCA es humillado ni condescendido; su arco es resistencia legítima → comprensión → acción voluntaria. La marca solo entra resolviendo una objeción, con features verificables. Duración objetivo 50–70 s (~15 turnos máx.). El CTA en este subformato convierte hacia el producto (lo modela el escéptico), no hacia comentarios.
                    TXT,
                ],
            ],
        ],

        'rankings' => [
            'instructions' => <<<'TXT'
            FORMATO «Rankings»: una lista ordenada (top N) sobre un criterio claro y polémico. Directrices:
            - Gancho con el número o la promesa del ranking ("Los 3 errores que…", "El nº1 te va a sorprender").
            - Orden ascendente hacia un clímax: reserva el elemento más fuerte/inesperado para el final.
            - Cada ítem breve y con un porqué; incluye al menos un giro o elección contraintuitiva que genere debate.
            - CTA a comentarios pidiendo lo que falta o el desacuerdo ("¿cuál agregarías?", "¿en qué puesto lo pondrías?").
            TXT,
            'subformats' => [],
        ],

        'selfie' => [
            'instructions' => <<<'TXT'
            FORMATO «Selfie» (opinión directa a cámara, cámara en mano): el creador da su opinión de frente. Directrices:
            - Gancho de tabú en la primera frase ("El 90% de X se enoja cuando digo esto").
            - La tesis, dicha de frente justo tras el gancho.
            - El creador se ADELANTA a las objeciones él mismo ("y ojo, no lo digo por…"), en vez de que las haga un escéptico.
            - Conserva la analogía cotidiana (es el activo más valioso de la idea).
            - Defiende explícitamente al "bando atacado suave" ("no digo que todo deba ser así") para no alienar.
            - CTA a comentarios con pregunta binaria ("¿tú qué opinas: sí o no?"). El debate es el motor del alcance.
            TXT,
            'subformats' => [],
        ],

        'hablando_a_camara' => [
            'instructions' => <<<'TXT'
            FORMATO «Hablando a cámara»: pieza a cámara, tono cercano y directo. Directrices:
            - Gancho con la afirmación polémica de frente.
            - Núcleo de "las 3 objeciones que siempre me hacen", enumeradas y respondidas (progresión lógica → práctica → personal).
            - Una analogía cotidiana central.
            - CTA híbrido (comentarios + enlace en bio) si encaja con el objetivo.
            TXT,
            'subformats' => [],
        ],

        'hablando_a_camara_visual' => [
            'instructions' => <<<'TXT'
            FORMATO «Hablando a cámara (visual)»: como hablando a cámara, pero apoyado en un elemento visual (pizarra, objeto, texto en pantalla). Directrices:
            - El gancho se MUESTRA además de decirse (afirmación escrita/objeto).
            - La analogía se encarna en el objeto/visual: muéstralo en el momento del clímax.
            - Las objeciones pueden ir numeradas en pantalla mientras se responden.
            TXT,
            'subformats' => [],
        ],

        'pov' => [
            'instructions' => <<<'TXT'
            FORMATO «POV» (primera persona): la cámara vive la situación; el espectador ES el protagonista/escéptico (p. ej. quien filma sostiene el celular como entrevistador y puede mostrar su propia mano al preguntar; el creador responde). Directrices:
            - Sitúa al espectador dentro de la escena desde el segundo 0 (texto en pantalla que define el POV).
            - La escalera de objeciones se convierte en una escalera de MICRO-ESCENAS emocionales: miedo → sorpresa → comodidad.
            - La analogía puede ir como texto en pantalla.
            - El producto aparece al final como el ORIGEN de la buena experiencia mostrada, no como anuncio.
            TXT,
            'subformats' => [],
        ],

        'podcast' => [
            'instructions' => <<<'TXT'
            FORMATO «Podcast» (entrevista simulada): se recrea un clip de podcast entre anfitrión e invitado (pueden ser el mismo creador). Directrices:
            - El anfitrión pregunta exactamente lo que la audiencia se pregunta; el invitado encarna a la marca/experto.
            - Escalera de preguntas cada vez más personales; respuestas cortas y firmes.
            - Arranca por el momento más fuerte (clip-gancho), no por presentaciones.
            - Setup reconocible (micrófono, dos sillas) para credibilidad de "conversación real".
            TXT,
            'subformats' => [],
        ],

        'puv' => [
            'instructions' => <<<'TXT'
            FORMATO «PUV / Entrevista en calle»: el escéptico deja de ser actuado — es gente real. Directrices:
            - La pregunta-gancho se le hace al entrevistado ("¿Pagarías por…?").
            - Las objeciones las producen los entrevistados; el creador responde en cortes o en la segunda mitad.
            - PROHIBIDO editar para ridiculizar respuestas (rompe la protección del escéptico/novato).
            - Cierra reencuadrando con la analogía y una acción concreta.
            TXT,
            'subformats' => [],
        ],

        'entrevista' => [
            'instructions' => <<<'TXT'
            FORMATO «Entrevista» (uno a uno): conversación con una persona real o experta. Directrices:
            - Preguntas que representan las dudas de la audiencia, en escalera.
            - Extrae 1–2 frases-clip potentes (gancho) y una analogía cotidiana.
            - Respeta al entrevistado; el conflicto es con la idea, no con la persona.
            TXT,
            'subformats' => [],
        ],

        'vlog' => [
            'instructions' => <<<'TXT'
            FORMATO «Vlog» (arco narrativo): la idea se estira a una historia documentada ("Voy a llevar a [escéptico real] a su primera vez con…"). Directrices:
            - Cada objeción del guion = un momento documentado: antes se dice a cámara, después se resuelve en la experiencia.
            - La conversión final del escéptico es literal y grabada: máxima prueba social.
            - Mayor costo de producción: reserva este formato para ideas ya validadas en formatos baratos.
            TXT,
            'subformats' => [],
        ],

        'documental_reto' => [
            'instructions' => <<<'TXT'
            FORMATO «Documental / Reto»: versión ampliada del arco narrativo, con estructura de reto ("X días haciendo…", "¿puede un principiante…?"). Directrices:
            - Planteamiento del reto como gancho + apuesta emocional clara.
            - Hitos y obstáculos (cada uno resuelve una objeción); tensión creciente hacia el desenlace.
            - Resolución con prueba social grabada y un solo CTA.
            - Alto costo: solo para ideas doblemente validadas.
            TXT,
            'subformats' => [],
        ],

    ],

];
