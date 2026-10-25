<?php

namespace Tests\Feature;

use App\Models\SlugRedirect;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class SlugHistoryTest extends MarketplaceTestCase
{
    public function test_old_product_urls_redirect_to_current_slug_without_chains(): void
    {
        $product = $this->offers[0]->product;
        $old = $product->slug;
        $product->update(['slug' => 'new-product-url']);
        $product->update(['slug' => 'latest-product-url']);
        $this->get('/products/'.$old)->assertStatus(301)->assertRedirect('/products/latest-product-url');
        $this->get('/products/new-product-url')->assertStatus(301)->assertRedirect('/products/latest-product-url');
        $this->get('/products/latest-product-url')->assertOk();
        $this->assertSame(2, SlugRedirect::count());
        $product->update(['status' => 'draft']);
        $this->get('/products/'.$old)->assertNotFound();
    }

    public function test_another_product_cannot_reclaim_a_historical_url(): void
    {
        $product = $this->offers[0]->product;
        $slug = $product->slug;
        $product->update(['slug' => 'renamed-product']);
        $this->expectException(ValidationException::class);
        $this->offers[1]->product->update(['slug' => $slug]);
    }
}
