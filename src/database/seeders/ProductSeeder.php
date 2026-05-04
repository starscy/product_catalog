<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем ID категорий для привязки
        $electronics = Category::where('name', 'Электроника')->first();
        $clothing = Category::where('name', 'Одежда')->first();
        $books = Category::where('name', 'Книги')->first();
        $home = Category::where('name', 'Дом и сад')->first();
        $sport = Category::where('name', 'Спорт')->first();

        $products = [
            // Электроника
            ['name' => 'Смартфон X', 'description' => '6.5" экран, 128GB', 'price' => 29999.99, 'category_id' => $electronics->id],
            ['name' => 'Ноутбук Pro', 'description' => '16GB RAM, 512GB SSD', 'price' => 69999.00, 'category_id' => $electronics->id],
            ['name' => 'Наушники Bluetooth', 'description' => 'Шумоподавление', 'price' => 4999.00, 'category_id' => $electronics->id],

            // Одежда
            ['name' => 'Футболка хлопок', 'description' => 'Белый, размер M', 'price' => 999.00, 'category_id' => $clothing->id],
            ['name' => 'Джинсы классические', 'description' => 'Синие, размер 32', 'price' => 3499.00, 'category_id' => $clothing->id],

            // Книги
            ['name' => 'Чистый код', 'description' => 'Роберт Мартин', 'price' => 1200.00, 'category_id' => $books->id],
            ['name' => 'Искусство программирования', 'description' => 'Кнут', 'price' => 3500.00, 'category_id' => $books->id],

            // Дом и сад
            ['name' => 'Лопата садовая', 'description' => 'Сталь, черенок дерево', 'price' => 800.00, 'category_id' => $home->id],
            ['name' => 'Горшок для цветов', 'description' => 'Пластик, 5л', 'price' => 250.00, 'category_id' => $home->id],

            // Спорт
            ['name' => 'Гантели 5 кг', 'description' => 'Пара, с резиновым покрытием', 'price' => 1999.00, 'category_id' => $sport->id],
            ['name' => 'Коврик для йоги', 'description' => 'Толщина 6мм', 'price' => 1500.00, 'category_id' => $sport->id],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}
