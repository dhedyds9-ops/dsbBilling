<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ISP\VoucherTemplate\Editor;
use App\Livewire\ISP\VoucherTemplate\Import;
use App\Livewire\ISP\VoucherTemplate\Index;
use App\Livewire\ISP\VoucherTemplate\Preview;
use App\Livewire\ISP\VoucherTemplate\Versions;
use App\Models\ISP\VoucherTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VoucherTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function createTemplate(array $attributes = [])
    {
        return VoucherTemplate::create(array_merge([
            'name' => 'Default Template',
            'category' => 'wifi',
            'description' => 'Test',
            'is_system' => false,
            'is_active' => true,
            'created_by' => $this->user->id,
        ], $attributes));
    }

    public function test_can_view_template_list()
    {
        $this->createTemplate(['name' => 'Test Template']);
        
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Test Template')
            ->assertStatus(200);
    }

    public function test_can_create_template()
    {
        Livewire::actingAs($this->user)
            ->test(Editor::class)
            ->set('name', 'New Template')
            ->set('category', 'wifi')
            ->set('template_code', '<p>Test</p>')
            ->call('save')
            ->assertRedirect();
            // ->assertHasNoErrors();
            
        $this->assertDatabaseHas('voucher_templates', ['name' => 'New Template']);
    }

    public function test_can_update_template()
    {
        $template = $this->createTemplate(['name' => 'Old Name', 'is_system' => false]);
        
        Livewire::actingAs($this->user)
            ->test(Editor::class, ['id' => $template->id])
            ->set('name', 'Updated Name')
            ->set('settings', ['changelog' => 'Changed name'])
            ->set('template_code', 'Updated content')
            ->call('save')->assertHasNoErrors();
            
        $this->assertDatabaseHas('voucher_templates', ['name' => 'Updated Name']);
        $this->assertDatabaseHas('voucher_template_versions', ['settings' => '{"changelog":"Changed name"}']);
    }

    public function test_system_template_protection()
    {
        $template = $this->createTemplate(['name' => 'System Template', 'is_system' => true]);
        
        Livewire::actingAs($this->user)
            ->test(Editor::class, ['id' => $template->id])
            ->assertRedirect(route('isp.voucher-templates.index'));
            
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $template->id);
            
        $this->assertDatabaseHas('voucher_templates', ['id' => $template->id]);
    }

    public function test_can_duplicate_template()
    {
        $template = $this->createTemplate(['name' => 'Base Template', 'is_system' => true]);
        
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('duplicate', $template->id)
            ->assertRedirect();
            // ->assertHasNoErrors();
            
        $this->assertDatabaseHas('voucher_templates', ['name' => 'Base Template (Copy)', 'is_system' => false]);
    }

    public function test_can_import_legacy_template()
    {
        Livewire::actingAs($this->user)
            ->test(Import::class)
            ->set('name', 'Legacy Test')
            ->set('category', 'wifi')
            ->set('legacyContent', '<div>{$vs["username"]}</div>')
            ->call('import')
            ->assertRedirect();
            // ->assertHasNoErrors();
            
        $this->assertDatabaseHas('voucher_templates', ['name' => 'Legacy Test']);
    }

    public function test_can_rollback_version()
    {
        $template = $this->createTemplate(['is_system' => false]);
        $version1 = $template->versions()->create(['version' => 1, 'template_code' => 'V1', 'created_by' => $this->user->id]);
        $version2 = $template->versions()->create(['version' => 2, 'template_code' => 'V2', 'created_by' => $this->user->id]);
        
        Livewire::actingAs($this->user)
            ->test(Versions::class, ['template' => $template])
            ->call('rollback', $version1->id)
            ->assertRedirect();
            // ->assertHasNoErrors();
            
        $this->assertDatabaseHas('voucher_template_versions', [
            'voucher_template_id' => $template->id,
            'version' => 3,
            'template_code' => 'V1'
        ]);
    }
}
