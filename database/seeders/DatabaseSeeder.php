<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Producción: SOLO catálogos globales (seguros). El contenido demo y los usuarios
     * con contraseña fija viven en DemoSeeder y solo se siembran en entorno `local`.
     * El admin real de producción se crea con `php artisan app:create-admin`.
     */
    public function run(): void
    {
        // Catálogo global de plantillas Heras (compartido por todas las marcas).
        $this->call(HerasTemplateSeeder::class);

        // Datos de ejemplo: solo en desarrollo, nunca en producción.
        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
