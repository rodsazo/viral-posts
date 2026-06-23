<?php

namespace App\Support;

/**
 * Lista curada de íconos FontAwesome para el selector de plantillas de gancho.
 * El valor guardado es la clase completa (p. ej. "fa-solid fa-fire"). Para verlos
 * renderizados, el panel debe cargar la CSS/Kit de FontAwesome (ver AdminPanelProvider).
 *
 * Amplía la lista libremente: clave = clase FA, valor = etiqueta legible.
 */
class FontAwesomeIcons
{
    /** @var array<string, string> */
    public const ICONS = [
        'fa-solid fa-fire' => 'Fuego',
        'fa-solid fa-bolt' => 'Rayo',
        'fa-solid fa-star' => 'Estrella',
        'fa-solid fa-heart' => 'Corazón',
        'fa-solid fa-eye' => 'Ojo',
        'fa-solid fa-lightbulb' => 'Bombilla / idea',
        'fa-solid fa-rocket' => 'Cohete',
        'fa-solid fa-brain' => 'Cerebro',
        'fa-solid fa-bullhorn' => 'Megáfono',
        'fa-solid fa-triangle-exclamation' => 'Advertencia',
        'fa-solid fa-circle-question' => 'Pregunta',
        'fa-solid fa-circle-exclamation' => 'Exclamación',
        'fa-solid fa-magnifying-glass' => 'Lupa',
        'fa-solid fa-bullseye' => 'Diana / objetivo',
        'fa-solid fa-trophy' => 'Trofeo',
        'fa-solid fa-crown' => 'Corona',
        'fa-solid fa-gem' => 'Gema',
        'fa-solid fa-money-bill-wave' => 'Dinero',
        'fa-solid fa-sack-dollar' => 'Saco de dinero',
        'fa-solid fa-chart-line' => 'Gráfico al alza',
        'fa-solid fa-arrow-trend-up' => 'Tendencia al alza',
        'fa-solid fa-hourglass-half' => 'Reloj de arena',
        'fa-solid fa-clock' => 'Reloj',
        'fa-solid fa-gift' => 'Regalo',
        'fa-solid fa-key' => 'Llave',
        'fa-solid fa-lock' => 'Candado',
        'fa-solid fa-shield-halved' => 'Escudo',
        'fa-solid fa-thumbs-up' => 'Pulgar arriba',
        'fa-solid fa-thumbs-down' => 'Pulgar abajo',
        'fa-solid fa-comment' => 'Comentario',
        'fa-solid fa-comments' => 'Conversación',
        'fa-solid fa-quote-left' => 'Cita',
        'fa-solid fa-skull' => 'Calavera',
        'fa-solid fa-ghost' => 'Fantasma',
        'fa-solid fa-mask' => 'Máscara',
        'fa-solid fa-face-surprise' => 'Sorpresa',
        'fa-solid fa-hand' => 'Mano (stop)',
        'fa-solid fa-hand-point-up' => 'Mano señalando',
        'fa-solid fa-dumbbell' => 'Pesa',
        'fa-solid fa-heart-pulse' => 'Salud / pulso',
        'fa-solid fa-apple-whole' => 'Manzana',
        'fa-solid fa-bed' => 'Cama / descanso',
        'fa-solid fa-pills' => 'Pastillas',
        'fa-solid fa-utensils' => 'Comida',
        'fa-solid fa-venus-mars' => 'Sexo / género',
        'fa-solid fa-fire-flame-curved' => 'Llama',
        'fa-solid fa-seedling' => 'Crecimiento',
        'fa-solid fa-person-walking' => 'Caminar',
        'fa-solid fa-book' => 'Libro',
        'fa-solid fa-graduation-cap' => 'Educación',
        'fa-solid fa-scroll' => 'Pergamino',
        'fa-solid fa-map' => 'Mapa',
        'fa-solid fa-compass' => 'Brújula',
        'fa-solid fa-flag' => 'Bandera',
        'fa-solid fa-wand-magic-sparkles' => 'Magia',
        'fa-solid fa-puzzle-piece' => 'Pieza de puzzle',
        'fa-solid fa-gears' => 'Engranajes',
        'fa-solid fa-list-check' => 'Lista',
        'fa-solid fa-circle-check' => 'Check',
        'fa-solid fa-ban' => 'Prohibido',
        'fa-solid fa-scale-balanced' => 'Balanza',
        'fa-solid fa-handshake' => 'Acuerdo',
        'fa-solid fa-users' => 'Personas',
        'fa-solid fa-user-secret' => 'Secreto',
        'fa-solid fa-clapperboard' => 'Claqueta / video',
        'fa-solid fa-camera' => 'Cámara',
        'fa-solid fa-microphone' => 'Micrófono',
        'fa-solid fa-play' => 'Reproducir',
    ];

    /**
     * Opciones para un Select de Filament con `allowHtml()`: clase => HTML (string) con ícono + etiqueta.
     * Debe devolver strings planos (no HtmlString): el componente los serializa a JS y un objeto
     * aparecería como "[object Object]".
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::ICONS as $class => $label) {
            $options[$class] = '<i class="'.e($class).' fa-fw"></i> &nbsp; '.e($label);
        }

        return $options;
    }
}
