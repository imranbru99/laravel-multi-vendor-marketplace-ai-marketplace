<?php

namespace Tests\Feature;

use App\Models\DigitalAsset;
use App\Models\DigitalEntitlement;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class DigitalCommerceTest extends MarketplaceTestCase
{
    protected function asset(): DigitalAsset
    {
        Storage::fake('local');
        $this->offers[0]->product->update(['type' => 'digital']);
        $path = 'tenants/'.$this->tenant->id.'/digital/guide.txt';
        Storage::disk('local')->put($path, 'Original demo digital content.');

        return DigitalAsset::create(['product_id' => $this->offers[0]->product_id, 'name' => 'Guide', 'path' => $path, 'rights_confirmed' => true]);
    }

    public function test_digital_capture_grants_private_access_and_skips_shipping(): void
    {
        $this->asset();
        $order = $this->place($this->cart(1, false));
        $this->assertSame(0, $order->shipping_minor);
        $this->capture($order);
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame(1, DigitalEntitlement::count());
        $this->capture($order);
        $this->assertSame(1, DigitalEntitlement::count());
    }

    public function test_signed_download_requires_owner_and_enforces_limit(): void
    {
        $this->asset();
        $order = $this->place($this->cart(1, false));
        $this->capture($order);
        $access = DigitalEntitlement::firstOrFail();
        $url = URL::temporarySignedRoute('digital.download', now()->addMinutes(10), ['id' => $access->id]);
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->customer)->get($url)->assertOk();
        }$this->get($url)->assertForbidden();
        $this->assertDatabaseHas('digital_entitlements', ['id' => $access->id, 'downloads' => 3]);
    }

    public function test_unsigned_download_is_forbidden(): void
    {
        $this->asset();
        $order = $this->place($this->cart(1, false));
        $this->capture($order);
        $this->actingAs($this->customer)->get('/downloads/'.DigitalEntitlement::first()->id)->assertForbidden();
    }

    public function test_other_customer_cannot_download_signed_asset(): void
    {
        $this->asset();
        $order = $this->place($this->cart(1, false));
        $this->capture($order);
        $access = DigitalEntitlement::first();
        $other = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Other', 'email' => 'digital-other@example.test', 'password' => 'TestPassword123']);
        $url = URL::temporarySignedRoute('digital.download', now()->addMinute(), ['id' => $access->id]);
        $this->actingAs($other)->get($url)->assertNotFound();
    }

    public function test_digital_asset_rejects_public_or_foreign_storage(): void
    {
        $this->expectException(ValidationException::class);
        DigitalAsset::create(['product_id' => $this->offers[0]->product_id, 'name' => 'Unsafe', 'path' => 'public/file.txt', 'rights_confirmed' => true]);
    }
}
