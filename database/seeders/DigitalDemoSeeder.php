<?php

namespace Database\Seeders;

use App\Domain\Inventory;
use App\Domain\TenantContext;
use App\Models\Booking;
use App\Models\Category;
use App\Models\DigitalAsset;
use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\Product;
use App\Models\TenantModule;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DigitalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing') || ! config('marketplace.demo')) {
            throw new \RuntimeException('Local demo only.');
        }
        TenantModule::firstOrCreate(['key' => 'digital'], ['enabled' => true]);
        if (Product::where('slug', 'independent-store-handbook')->exists()) {
            return;
        }
        $vendor = Vendor::firstOrFail();
        $category = Category::firstOrFail();
        $warehouse = Warehouse::where('vendor_id', $vendor->id)->firstOrFail();
        foreach (['digital' => ['Independent store handbook', 'independent-store-handbook', 'book', 1900], 'service' => ['A considered space consultation', 'space-consultation', 'desk', 5000]] as $type => [$name,$slug,$art,$price]) {
            $product = Product::create(['category_id' => $category->id, 'name' => $name, 'slug' => $slug, 'description' => 'Simulated '.$type.' offering for demonstration. No real service is provided.', 'type' => $type, 'status' => 'published', 'image' => '/images/'.$art.'.svg']);
            $offer = Offer::create(['vendor_id' => $vendor->id, 'product_id' => $product->id, 'sku' => 'DEMO-'.strtoupper($type).'-'.app(TenantContext::class)->id(), 'price_minor' => $price]);
            $stock = InventoryBalance::create(['vendor_id' => $vendor->id, 'offer_id' => $offer->id, 'warehouse_id' => $warehouse->id]);
            app(Inventory::class)->change($stock, 100, 0, 'demo entitlement allocation', 'digital-demo:'.$stock->id);
            if ($type === 'digital') {
                $path = 'tenants/'.app(TenantContext::class)->id().'/digital/independent-store-handbook.txt';
                Storage::disk('local')->put($path, "Independent Store Handbook — demonstration edition\n\n1. Keep seller offers separate from master products.\n2. Record every inventory movement.\n3. Preserve order pricing snapshots.\n4. Reconcile balanced financial journals.\n\nOriginal demonstration content by Imran Ahmed https://imrandev.bd\nPermission is granted to use this sample with this marketplace demo.\n");
                DigitalAsset::create(['product_id' => $product->id, 'name' => 'Handbook · Demo edition', 'path' => $path, 'rights_confirmed' => true]);
            } else {
                $user = User::where('tenant_id', app(TenantContext::class)->id())->where('role', 'customer')->firstOrFail();
                Booking::create(['user_id' => $user->id, 'vendor_id' => $vendor->id, 'offer_id' => $offer->id, 'starts_at' => now()->addDays(3)->startOfHour()]);
            }
        }
    }
}
