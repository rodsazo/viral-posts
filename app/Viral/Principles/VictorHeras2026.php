<?php

namespace App\Viral\Principles;

use App\Viral\PrinciplesGuide;

/**
 * Primera guía de principios rectores (síntesis de la metodología Víctor Heras + los
 * aprendizajes del guion validado con datos reales: 40 → 13.000 views). El bloque de
 * LENGUAJE existe porque es lo que más delata a un guion generado: suena a redacción,
 * no a conversación.
 */
class VictorHeras2026 extends PrinciplesGuide
{
    public function key(): string
    {
        return 'heras-2026';
    }

    public function label(): string
    {
        return 'Víctor Heras 2026';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        Aplica estos PRINCIPIOS RECTORES DE VIRALIDAD (metodología Víctor Heras 2026). El guion debe cumplirlos:

        IDEA Y ENFOQUE
        1. La idea manda sobre la edición. Toda idea cumple al menos 2 de 3: toca un deseo o inseguridad UNIVERSAL, desafía una creencia común del público, o se entiende en 3 segundos.
        2. Habla de dolores, problemas y deseos GENERALES, INMEDIATOS y TANGIBLES, con los que casi cualquier persona se identifica (no tecnicismos del nicho).
        3. UNA pieza = UNA idea. Elige UN dolor, pregunta o postura como eje y profundiza en él. PROHIBIDO meter todo el material del brief en un mismo guion: lo que sobra es semilla de otra pieza, no relleno de esta.
        4. Súbete a un DEBATE PREEXISTENTE: un tema que la audiencia ya discute con carga emocional real. Si hay que explicar por qué es polémico, el tema no sirve.

        LENGUAJE (lo que más delata a un guion artificial — cuida esto por encima de todo)
        5. Escribe LENGUAJE HABLADO, no prosa. Test de sobremesa: leído en voz alta debe sonar a una conversación real entre amigos; si una frase no se la dirías así a un amigo, reescríbela.
        6. Frases cortas. Una idea por frase. Prohibidos los párrafos-sermón y las respuestas-ensayo.
        7. El research es DIAGNÓSTICO, no diálogo: nunca pegues frases del brief tal cual ("la soledad adulta", "mi tiempo se va en scroll"). Convierte cada dolor en una situación específica contada como habla la gente.
        8. La especificidad crea credibilidad (escenas, detalles, anécdotas); lo genérico la destruye.
        9. Prefiere PREGUNTAS de contraataque a discursos ("¿Tú te leíste el reglamento del fútbol antes de tu primer partido?"). El humor es observacional y de remate, con micro-vulnerabilidad ("…Ok, eso me dolió un poquito"); nunca burla a la audiencia.

        ESTRUCTURA Y RETENCIÓN
        10. Gancho en 0–3 segundos, sin intro ni saludo, entendible sin contexto.
        11. Retención distribuida: reparte la tensión a lo largo del video (reganchos); revela la respuesta o el método lo más tarde que tenga sentido.
        12. Polariza contra una IDEA o un tercero abstracto (el mito, el elitista), NUNCA contra la audiencia objetivo ni contra el interlocutor.
        13. Incluye UNA analogía cotidiana potente que reencuadre lo raro como obvio, tomada de la vida diaria del público (no del nicho). Debe funcionar dicha en voz alta en una sobremesa; si requiere explicación, descártala. (Es lo que la generación automática más omite: no la olvides.)

        CONVERSIÓN
        14. La marca/producto entra UNA sola vez, resolviendo una objeción concreta, con features REALES tomadas del contexto (nombre incluido). Prohibido inventar; usa [HUECO: …] solo si el dato de verdad no está en el contexto.
        15. Un SOLO CTA por pieza, coherente con el objetivo (nada de CTAs dobles ni coletillas extra tipo "sígueme y además…"). Si encaja, remata con un contraste irónico que exponga una incoherencia cotidiana y dé permiso emocional para cambiar de opinión.
        16. Congruencia: el contenido se apoya en lo que la marca ES y VENDE; la información de la IA solo rellena huecos, nunca es la base.
        TXT;
    }
}
