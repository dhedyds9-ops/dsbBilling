<?php

namespace Tests\Feature;

use App\Models\Inventory\Item;
use App\Models\User;
use App\Livewire\Inventory\Item\Index;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryItemSkuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Buat user admin
        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    public function test_create_modal_automatically_suggests_sequential_sku()
    {
        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->assertSet('sku', 'ONT-0001')
            ->assertSet('category', 'ONU/ONT');
    }

    public function test_changing_category_updates_sku_prefix_and_unit()
    {
        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->assertSet('sku', 'ONT-0001')
            ->set('category', 'Router')
            ->assertSet('sku', 'RTR-0001')
            ->assertSet('unit', 'unit')
            ->set('category', 'Kabel Dropcore')
            ->assertSet('sku', 'CBL-0001')
            ->assertSet('unit', 'meter');
    }

    public function test_smart_sku_chips_recommendation_based_on_brand_and_model()
    {
        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->set('category', 'ONU/ONT')
            ->set('brand', 'ZTE')
            ->set('model', 'F609 V3')
            ->call('applySuggestedSku', 'DEV-ONT-ZTE-F609-V3')
            ->assertSet('sku', 'DEV-ONT-ZTE-F609-V3');
    }

    public function test_refresh_suggested_sku_recalculates_next_number()
    {
        Item::create([
            'sku' => 'ONT-0001',
            'name' => 'ONT 1',
            'category' => 'ONU/ONT',
            'unit' => 'unit',
            'min_stock' => 5,
        ]);

        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->assertSet('sku', 'ONT-0002');
    }

    public function test_category_filter_matches_both_spaceless_and_spaced_onu_categories()
    {
        // 1 item dengan kategori ONU/ONT (tanpa spasi)
        $item1 = Item::create([
            'sku' => 'ONT-0001',
            'name' => 'ONT GPON HG6045F1',
            'category' => 'ONU/ONT',
            'unit' => 'unit',
            'min_stock' => 5,
            'is_active' => true,
        ]);

        // 1 item langsung di DB dengan variasi spasi 'ONU / ONT'
        $item2Id = \Illuminate\Support\Facades\DB::table('items')->insertGetId([
            'sku' => 'ONT-0002',
            'name' => 'ONT GPON FIBERHOME HG6045F3',
            'category' => 'ONU / ONT',
            'unit' => 'unit',
            'min_stock' => 5,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Filter kategori 'ONU/ONT' harus menangkap KEDUA item tanpa tertinggal
        Livewire::test(Index::class)
            ->set('categoryFilter', 'ONU/ONT')
            ->assertSee('ONT GPON HG6045F1')
            ->assertSee('ONT GPON FIBERHOME HG6045F3');
    }
}
