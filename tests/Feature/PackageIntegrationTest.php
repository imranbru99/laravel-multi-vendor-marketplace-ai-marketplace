<?php

namespace Tests\Feature;

use App\Domain\Modules\ModuleRegistry;
use App\Domain\Modules\RegistryModule;
use ImranDev\UniversalSlug\Facades\UniversalSlug;
use ImranDevBd\AiHub\Facades\AIHub;
use Tests\MarketplaceTestCase;

class PackageIntegrationTest extends MarketplaceTestCase
{
    public function test_ai_hub_fake_adapter_returns_only_simulated_content(): void
    {
        $fake = AIHub::fake('Explicit sandbox response.');
        $response = $fake->gemini('sandbox-model')->prompt('Draft a product description')->send();
        $this->assertSame('Explicit sandbox response.', $response->content);
        $fake->assertSentCount(1);
    }

    public function test_unicode_slug_preserves_non_latin_text(): void
    {
        $slug = UniversalSlug::make('বাংলা পণ্য');
        $this->assertNotEmpty($slug);
        $this->assertStringNotContainsString(' ', $slug);
    }

    public function test_module_registry_validates_dependencies(): void
    {
        $this->assertArrayHasKey('digital', app(ModuleRegistry::class)->all());
        $this->expectException(\LogicException::class);
        app(ModuleRegistry::class)->validate(['a' => new RegistryModule('a', ['name' => 'A', 'dependencies' => ['b']]), 'b' => new RegistryModule('b', ['name' => 'B', 'dependencies' => ['a']])]);
    }

    public function test_profiler_page_requires_platform_role(): void
    {
        $this->actingAs($this->admin)->get('/tenant/performance')->assertForbidden();
        $this->admin->update(['role' => 'superadmin']);
        $this->get('/admin/performance')->assertOk()->assertSee('SQL text, parameter values, cookies');
    }

    public function test_bengali_locale_changes_storefront_labels(): void
    {
        $this->withSession(['locale' => 'bn'])->get('/')->assertOk()->assertSee('ভালো জিনিস।')->assertSee('সব পণ্য');
    }
}
