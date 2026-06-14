<?php

namespace Database\Seeders;

use App\Enums\BeliefType;
use App\Enums\ContentFormat;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\Belief;
use App\Models\Category;
use App\Models\ContentPiece;
use App\Models\HerasTemplate;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Catálogo global de plantillas Heras (compartido por todas las marcas).
        $this->call(HerasTemplateSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'rodsazo@gmail.com'],
            ['name' => 'El Rod', 'password' => Hash::make('password')],
        );

        $account = Account::firstOrCreate(
            ['slug' => 'el-rod-y-el-rol'],
            ['name' => 'El Rod y El Rol', 'description' => 'Atraer gente nueva al rol de mesa con contenido viral.'],
        );
        $account->users()->syncWithoutDetaching([$user->id]);

        // Evitar duplicar el contenido demo si el seeder se ejecuta de nuevo.
        if ($account->questions()->exists()) {
            return;
        }

        // --- Categorías (por marca) ---
        $miedos = Category::create(['account_id' => $account->id, 'name' => 'Miedos e inseguridades', 'color' => '#ef4444']);
        $grupo = Category::create(['account_id' => $account->id, 'name' => 'Encontrar grupo', 'color' => '#3b82f6']);
        Category::create(['account_id' => $account->id, 'name' => 'Cómo empezar', 'color' => '#22c55e']);

        // --- Seguidor ideal ---
        $follower = IdealFollower::create([
            'account_id' => $account->id,
            'name' => 'Grupo de amigos curiosos',
            'description' => 'Amigos que han oído hablar del rol de mesa y les pica la curiosidad, pero nadie sabe dirigir ni por dónde empezar.',
        ]);

        // --- Preguntas ---
        $q1 = Question::create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'category_id' => $miedos->id,
            'body' => '¿No es muy difícil aprender a jugar a rol de mesa?',
        ]);
        $q2 = Question::create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'category_id' => $grupo->id,
            'body' => '¿Y si ninguno de mi grupo sabe ser máster?',
        ]);
        $q3 = Question::create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'category_id' => $miedos->id,
            'body' => '¿No es algo solo para frikis muy metidos en el tema?',
        ]);

        // --- Mitos y verdades + relación N:M con preguntas ---
        $myth1 = Belief::create([
            'account_id' => $account->id,
            'type' => BeliefType::Myth,
            'statement' => 'El rol de mesa es complicadísimo y requiere leer manuales enormes.',
            'stance' => 'Se puede empezar a jugar en 10 minutos con reglas mínimas.',
        ]);
        $myth2 = Belief::create([
            'account_id' => $account->id,
            'type' => BeliefType::Myth,
            'statement' => 'Necesitas que alguien del grupo sea un máster experto.',
            'stance' => 'Existen GMs profesionales que dirigen para grupos nuevos.',
        ]);
        $truth1 = Belief::create([
            'account_id' => $account->id,
            'type' => BeliefType::Truth,
            'statement' => 'Cualquiera puede disfrutar del rol con una buena primera partida guiada.',
            'stance' => 'Impulsar la idea de "prueba una partida para principiantes".',
        ]);

        $q1->beliefs()->attach([$myth1->id, $truth1->id]);
        $q2->beliefs()->attach([$myth2->id]);
        $q3->beliefs()->attach([$myth1->id, $truth1->id]);

        // --- Idea ganadora + relación N:M con preguntas (alimenta el multi-salto) ---
        $idea = WinningIdea::create([
            'account_id' => $account->id,
            'heras_template_id' => HerasTemplate::where('number', 1)->value('id'),
            'title' => 'Tu primera partida en 10 minutos',
            'concept' => 'Desmontar el mito de la dificultad mostrando una partida real arrancando de cero con amigos.',
            'viral_mechanism' => 'Sorpresa / mito vs realidad',
        ]);
        $idea->questions()->attach([$q1->id, $q3->id]);

        $idea2 = WinningIdea::create([
            'account_id' => $account->id,
            'title' => 'Solicita un GM y juega esta semana',
            'concept' => 'Para grupos sin máster: cómo conseguir un GM en MesasRoleras y jugar ya.',
            'viral_mechanism' => 'Solución directa a una objeción',
        ]);
        $idea2->questions()->attach([$q2->id]);

        // --- Piezas de contenido ---
        ContentPiece::create([
            'account_id' => $account->id,
            'winning_idea_id' => $idea->id,
            'title' => 'Reel: enseñamos a 3 amigos en 10 min',
            'format' => ContentFormat::DocumentalReto,
            'status' => ContentStatus::GuionListo,
            'hook' => '"El rol es dificilísimo"... les puse a jugar sin leer nada.',
            'story' => 'Sentamos a tres amigos que nunca habían jugado y arrancamos una escena.',
            'moral' => 'No necesitas manuales: necesitas empezar.',
            'cta' => 'Descubre partidas para principiantes en MesasRoleras.com',
        ]);

        ContentPiece::create([
            'account_id' => $account->id,
            'winning_idea_id' => $idea2->id,
            'title' => 'Vídeo: cómo solicitar un GM',
            'format' => ContentFormat::HablandoACamara,
            'status' => ContentStatus::Publicada,
            'hook' => '¿Nadie de tu grupo quiere ser máster? Tengo la solución.',
            'cta' => 'Solicita un GM en MesasRoleras.com',
            'url' => 'https://example.com/video-gm',
            'rating' => ContentRating::Buena,
        ]);

        // Pieza "suelta" sin idea ganadora (decisión #5).
        ContentPiece::create([
            'account_id' => $account->id,
            'winning_idea_id' => null,
            'title' => 'Idea suelta: bloopers de partidas',
            'format' => ContentFormat::Vlog,
            'status' => ContentStatus::Planificacion,
        ]);
    }
}
