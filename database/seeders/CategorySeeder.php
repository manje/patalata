<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            ['nombre' => 'Tecnología', 'slug' => 'tecnologia', 'estado' => 'activo'],
            ['nombre' => 'Medio Ambiente', 'slug' => 'medio-ambiente', 'estado' => 'activo'],
            ['nombre' => 'Educación', 'slug' => 'educacion', 'estado' => 'activo'],
            ['nombre' => 'Salud', 'slug' => 'salud', 'estado' => 'activo'],
            ['nombre' => 'Derechos Humanos', 'slug' => 'derechos-humanos', 'estado' => 'activo'],
            ['nombre' => 'Economía', 'slug' => 'economia', 'estado' => 'activo'],
            ['nombre' => 'Política', 'slug' => 'politica', 'estado' => 'activo'],
            ['nombre' => 'Cultura', 'slug' => 'cultura', 'estado' => 'activo'],
            ['nombre' => 'Migraciones', 'slug' => 'migraciones', 'estado' => 'activo'],
            ['nombre' => 'Feminismos', 'slug' => 'feminismos', 'estado' => 'activo'],
            ['nombre' => 'Urbanismo', 'slug' => 'urbanismo', 'estado' => 'activo'],
            ['nombre' => 'Vivienda', 'slug' => 'vivienda', 'estado' => 'activo'],
            ['nombre' => 'Racismo', 'slug' => 'racismo', 'estado' => 'activo'],
            ['nombre' => 'Democracia', 'slug' => 'democracia', 'estado' => 'activo'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
