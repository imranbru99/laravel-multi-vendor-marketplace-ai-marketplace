<?php

namespace Tests\Feature;

use App\Domain\ModuleManager;
use App\Domain\TenantContext;
use App\Filament\Pages\Modules;
use App\Filament\ResourcePages\ManageBrand;
use App\Http\Middleware\ResolveTenant;
use App\Models\Brand;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Tests\MarketplaceTestCase;

class LivewireOperationsTest extends MarketplaceTestCase
{
    public function test_nested_persistent_middleware_restores_outer_context(): void
    {
        $request = Request::create('/livewire/update', 'POST');
        $middleware = new ResolveTenant;
        $middleware->handle($request, function () use ($middleware, $request) {
            $middleware->handle($request, fn () => response('nested'));
            $this->assertSame($this->tenant->id, app(TenantContext::class)->id());

            return response('outer');
        });
        $this->assertSame($this->tenant->id, app(TenantContext::class)->id());
    }

    public function test_module_livewire_toggle_updates_database_and_preserves_drafts(): void
    {
        $this->actingAs($this->admin);
        Livewire::test(Modules::class)->call('toggle', 'ai')->assertHasNoErrors();
        $this->assertFalse(app(ModuleManager::class)->enabled('ai'));
        Livewire::test(Modules::class)->call('toggle', 'ai')->assertHasNoErrors();
        $this->assertTrue(app(ModuleManager::class)->enabled('ai'));
    }

    public function test_catalog_create_modal_persists_a_real_brand(): void
    {
        $this->actingAs($this->admin);
        Filament::setCurrentPanel(Filament::getPanel('tenant'));
        Livewire::test(ManageBrand::class)->callAction('create', data: ['name' => 'New Brand', 'slug' => 'new-brand'])->assertHasNoActionErrors();
        $this->assertDatabaseHas('brands', ['name' => 'New Brand', 'tenant_id' => $this->tenant->id]);
    }

    public function test_customer_cannot_invoke_module_action(): void
    {
        $this->actingAs($this->customer);
        Livewire::test(Modules::class)->assertForbidden();
    }

    public function test_duplicate_slug_is_a_form_error_instead_of_a_database_failure(): void
    {
        $this->actingAs($this->admin);
        Filament::setCurrentPanel(Filament::getPanel('tenant'));
        Brand::create(['name' => 'Existing', 'slug' => 'duplicate']);
        Livewire::test(ManageBrand::class)->callAction('create', data: ['name' => 'Another', 'slug' => 'duplicate'])->assertHasActionErrors(['slug' => 'unique']);
        $this->assertDatabaseCount('brands', 1);
    }
}
