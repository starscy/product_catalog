<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём тестового пользователя
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);

        // Создаём тестовую категорию
        $this->category = Category::factory()->create([
            'name' => 'Test Category'
        ]);

        // Создаём тестовый товар
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 999.99,
            'category_id' => $this->category->id
        ]);
    }

    #[Test]
    public function public_catalog_page_displays_products(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Products/Index')
            ->has('products')
            ->has('categories')
            ->has('filters')
        );
    }

    #[Test]
    public function public_catalog_filters_by_category(): void
    {
        $anotherCategory = Category::factory()->create(['name' => 'Another Category']);
        $productInAnotherCategory = Product::factory()->create([
            'category_id' => $anotherCategory->id
        ]);

        $response = $this->get(route('home', ['category_id' => $this->category->id]));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];

        $this->assertTrue(
            collect($products)->every(fn($p) => $p['category_id'] === $this->category->id)
        );

        $this->assertFalse(
            collect($products)->contains('id', $productInAnotherCategory->id)
        );
    }

    #[Test]
    public function public_catalog_sorts_by_price_asc(): void
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 1000]);

        $response = $this->get(route('home', [
            'sort_by' => 'price',
            'direction' => 'asc'
        ]));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];
        $prices = collect($products)->pluck('price')->toArray();

        $this->assertEquals([100, 999.99, 1000], $prices);
    }

    #[Test]
    public function public_catalog_sorts_by_price_desc(): void
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 1000]);

        $response = $this->get(route('home', [
            'sort_by' => 'price',
            'direction' => 'desc'
        ]));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];
        $prices = collect($products)->pluck('price')->toArray();

        $this->assertEquals([1000, 999.99, 100], $prices);
    }

    #[Test]
    public function public_catalog_paginates_results(): void
    {
        // Создаём 15 товаров (пагинация по 12)
        Product::factory()->count(14)->create();

        $response = $this->get(route('home'));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];
        $this->assertCount(12, $products);
    }

    #[Test]
    public function admin_index_displays_products(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Admin/Products/Index')
            ->has('products')
            ->has('filters')
        );
    }

    #[Test]
    public function admin_index_shows_only_trashed_products(): void
    {
        $activeProduct = Product::factory()->create();
        $trashedProduct = Product::factory()->create();
        $trashedProduct->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['trashed' => true]));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];

        $this->assertTrue(
            collect($products)->every(fn($p) => $p['deleted_at'] !== null)
        );

        $this->assertFalse(
            collect($products)->contains('id', $activeProduct->id)
        );
    }

    #[Test]
    public function admin_index_searches_products_by_name(): void
    {
        $matchingProduct = Product::factory()->create(['name' => 'Unique Search Term']);
        $nonMatchingProduct = Product::factory()->create(['name' => 'Other Product']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['search' => 'Unique Search Term']));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];

        $this->assertTrue(
            collect($products)->contains('id', $matchingProduct->id)
        );

        $this->assertFalse(
            collect($products)->contains('id', $nonMatchingProduct->id)
        );
    }

    #[Test]
    public function admin_index_searches_products_by_description(): void
    {
        $matchingProduct = Product::factory()->create([
            'description' => 'Contains unique keyword here'
        ]);
        $nonMatchingProduct = Product::factory()->create([
            'description' => 'Regular description'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['search' => 'unique keyword']));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];

        $this->assertTrue(
            collect($products)->contains('id', $matchingProduct->id)
        );

        $this->assertFalse(
            collect($products)->contains('id', $nonMatchingProduct->id)
        );
    }

    #[Test]
    public function admin_index_filters_by_category(): void
    {
        $anotherCategory = Category::factory()->create();
        $productInAnotherCategory = Product::factory()->create([
            'category_id' => $anotherCategory->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['category_id' => $this->category->id]));

        $response->assertStatus(200);

        $products = $response->viewData('page')['props']['products']['data'];

        $this->assertTrue(
            collect($products)->every(fn($p) => $p['category_id'] === $this->category->id)
        );

        $this->assertFalse(
            collect($products)->contains('id', $productInAnotherCategory->id)
        );
    }

    #[Test]
    public function product_show_page_displays_product_details(): void
    {
        $response = $this->get(route('products.show', $this->product));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Products/Show')
            ->has('product')
            ->where('product.id', $this->product->id)
            ->where('product.name', 'Test Product')
            ->where('product.price', 999.99)
        );
    }

    #[Test]
    public function create_product_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Products/Create')
            ->has('categories')
        );
    }

    #[Test]
    public function store_creates_new_product(): void
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'New Description',
            'price' => 499.99,
            'category_id' => $this->category->id
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('products.store'), $productData);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Товар создан!');

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'description' => 'New Description',
            'price' => 499.99,
            'category_id' => $this->category->id
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('products.store'), []);

        $response->assertSessionHasErrors(['name', 'description', 'price', 'category_id']);
    }

    #[Test]
    public function store_validates_name_max_length(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => str_repeat('a', 256),
                'description' => 'Valid description',
                'price' => 100,
                'category_id' => $this->category->id
            ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function store_validates_price_is_numeric_and_positive(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Valid Name',
                'description' => 'Valid description',
                'price' => -10,
                'category_id' => $this->category->id
            ]);

        $response->assertSessionHasErrors('price');
    }

    #[Test]
    public function edit_form_displays_product_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('products.edit', $this->product));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Products/Edit')
            ->has('product')
            ->has('categories')
            ->where('product.id', $this->product->id)
            ->where('product.name', 'Test Product')
        );
    }

    #[Test]
    public function update_modifies_existing_product(): void
    {
        $updatedData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated Description',
            'price' => 199.99,
            'category_id' => $this->category->id
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('products.update', $this->product), $updatedData);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Товар обновлён!');

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'name' => 'Updated Product Name',
            'description' => 'Updated Description',
            'price' => 199.99
        ]);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('products.update', $this->product), []);

        $response->assertSessionHasErrors(['name', 'description', 'price', 'category_id']);
    }

    #[Test]
    public function destroy_soft_deletes_product(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('products.destroy', $this->product));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Товар удалён!');

        $this->assertSoftDeleted('products', [
            'id' => $this->product->id
        ]);
    }

    #[Test]
    public function admin_index_requires_authentication(): void
    {
        $response = $this->get(route('admin.products.index'));
        $response->assertRedirect('/login');
    }

    #[Test]
    public function create_form_requires_authentication(): void
    {
        $response = $this->get(route('products.create'));
        $response->assertRedirect('/login');
    }

    #[Test]
    public function store_requires_authentication(): void
    {
        $response = $this->post(route('products.store'), []);
        $response->assertRedirect('/login');
    }

    #[Test]
    public function edit_form_requires_authentication(): void
    {
        $response = $this->get(route('products.edit', $this->product));
        $response->assertRedirect('/login');
    }

    #[Test]
    public function update_requires_authentication(): void
    {
        $response = $this->put(route('products.update', $this->product), []);
        $response->assertRedirect('/login');
    }

    #[Test]
    public function destroy_requires_authentication(): void
    {
        $response = $this->delete(route('products.destroy', $this->product));
        $response->assertRedirect('/login');
    }
}
