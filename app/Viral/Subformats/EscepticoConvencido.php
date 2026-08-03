<?php

namespace App\Viral\Subformats;

use App\Viral\Subformat;

/**
 * El subformato validado con datos reales (40 → 13.000 views). Las reglas de forma
 * (turnos cortos, un tema por guion, preguntas de contraataque) salen de comparar el
 * guion ganador con salidas artificiales: son las que separan un guion que suena a
 * conversación de uno que suena a redacción.
 */
class EscepticoConvencido extends Subformat
{
    public function key(): string
    {
        return 'esceptico-convencido';
    }

    public function label(): string
    {
        return 'Diálogo escéptico ↔ convencido';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        SUBFORMATO «Diálogo escéptico ↔ convencido»: el ESCÉPTICO (personaje 1) encarna las dudas y objeciones reales de la audiencia; el CONVENCIDO (personaje 2) encarna a la marca/solución. Pueden ser el mismo creador interpretando ambos.

        FORMA DE ESCRITURA (obligatoria):
        - Todo el guion son turnos de diálogo numerados, uno por línea: «1 —» (escéptico) y «2 —» (convencido).
        - Turnos CORTOS: 1 a 3 frases; un turno = una sola idea. Prohibidos los turnos-ensayo: si no se puede decir de un tirón, se corta en dos turnos.
        - 10 a 16 turnos en total (~50–70 segundos hablados).
        - Mapa a los campos del guion: GANCHO = el primer turno del escéptico + la afirmación firme del convencido. HISTORIA = la escalera de objeciones (turnos intermedios). MORALEJA = el clímax: la analogía desbloqueadora y la resolución de la última barrera (aquí entra la marca). CTA = los 2 turnos finales (remate + el escéptico toma la acción) y, en línea aparte, «CTA en pantalla: …».

        ESTRUCTURA (en orden):
        1. Pregunta escéptica de apertura (0–2 s, sin intro): la duda dicha como la diría alguien real, con incredulidad ("¿En serio andas diciendo que…?", "Sé honesto, ¿para qué…?"). Entendible sin contexto en 3 s.
        2. Afirmación firme y corta del convencido: planta bandera sin rodeos ni disculpas ("Sí. Para tu primera partida no necesitas leer absolutamente nada.").
        3. Escalera de objeciones (3–5 rondas): cada objeción nace de la respuesta anterior y ESCALA de lógica → práctica → personal/emocional. No es una lista de FAQs sueltas.
        4. Analogía desbloqueadora (clímax): UNA comparación de la vida cotidiana del público (no del nicho) que reencuadra lo raro como obvio. Mejor si el convencido la plantea como PREGUNTA de contraataque ("¿Tú te leíste el reglamento del fútbol antes de tu primer partido en la calle?") y deja que el escéptico complete la conclusión.
        5. Última barrera práctica (acceso/precio/riesgo) → la marca aparece UNA sola vez resolviéndola, nombrada por su nombre y con features reales del contexto. Si el nombre o una feature no están en el contexto, usa [HUECO: …]; nunca inventes.
        6. Remate + conversión: si encaja, un contraste irónico que expone una incoherencia cotidiana; y el ESCÉPTICO cierra tomando la acción en primera persona, a regañadientes y con humor si sale natural ("…Ok, eso me dolió un poquito. Voy a buscar un GM de esos."). Un solo CTA; nada de coletillas extra ("sígueme y además…").

        REGLAS DEL ESCÉPTICO (las que más se violan):
        - Cada objeción pasa el test: "¿una persona real escéptica diría esto, con estas palabras?". Objeciones legítimas, específicas y personales (anécdota > abstracción); nada de hombres de paja.
        - Habla como persona: interrumpe, duda, cede a medias ("No, pero…", "Bueno, visto así…", "…Eso qué tiene que ver.").
        - NUNCA es humillado, ridiculizado ni tratado con condescendencia. Gana él: se convence por lógica y actúa por voluntad propia.

        REGLAS DEL CONVENCIDO:
        - Responde corto y firme; prefiere preguntas de contraataque a discursos. Cero sermones.
        - Sin sarcasmo contra el escéptico, sin "es obvio". El enemigo es una idea o un tercero abstracto (el elitista, el mito), jamás el interlocutor.
        - Traduce toda jerga en la misma frase ("el GameMaster — el que dirige la historia").

        FOCO (crítico):
        - CADA guion ataca UNA sola postura/objeción central. Un tema = una pieza. Si se piden varias variantes, cada una toma un tema central DISTINTO (distinta postura, distinto dolor); lo que no quepa es material para otra pieza, no relleno de esta.
        - El CTA de este subformato convierte hacia el producto/acción (lo modela el escéptico convencido), no hacia comentarios.
        - La firma verbal del personaje (si existe) se usa como cierre solo si suena natural en esa pieza; no la repitas mecánicamente en todas.
        TXT;
    }
}
