<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Электроника', 'description' => 'Смартфоны, ноутбуки, аксессуары'],
            ['name' => 'Одежда', 'description' => 'Мужская, женская, детская одежда'],
            ['name' => 'Книги', 'description' => 'Художественная литература, саморазвитие'],
            ['name' => 'Дом и сад', 'description' => 'Мебель, инструменты, растения'],
            ['name' => 'Спорт', 'description' => 'Тренажёры, экипировка, питание'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
