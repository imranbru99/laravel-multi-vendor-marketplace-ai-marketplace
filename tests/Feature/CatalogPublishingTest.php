<?php

namespace Tests\Feature;

use App\Domain\CatalogImporter;
use App\Domain\CatalogPublishing;
use App\Models\CatalogImport;
use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductProposal;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class CatalogPublishingTest extends MarketplaceTestCase
{
    private function proposal(array $extra = []): ProductProposal
    {
        return ProductProposal::create($extra + ['vendor_id' => $this->offers[0]->vendor_id, 'warehouse_id' => $this->stocks[0]->warehouse_id, 'category_id' => $this->offers[0]->product->category_id, 'name' => 'Seller proposed item', 'description' => 'Original licensed seller description.', 'sku' => 'NEW-SELLER-ITEM', 'price_minor' => 2500, 'initial_quantity' => 8, 'rights_confirmed' => true]);
    }

    public function test_seller_submission_requires_review_then_publishes_a_real_offer_and_stock(): void
    {
        $p = $this->proposal();
        app(CatalogPublishing::class)->submit($p);
        $this->assertSame('pending', $p->fresh()->status);
        $this->assertSame(2, Product::count());
        $product = app(CatalogPublishing::class)->approve($p);
        $this->assertSame('published', $product->status);
        $offer = Offer::where('sku', 'NEW-SELLER-ITEM')->firstOrFail();
        $this->assertSame(8, InventoryBalance::where('offer_id', $offer->id)->first()->quantity);
        $this->assertSame('approved', $p->fresh()->status);
        $this->assertSame($product->id, app(CatalogPublishing::class)->approve($p)->id);
    }

    public function test_unlicensed_submission_is_not_accepted(): void
    {
        $p = $this->proposal(['rights_confirmed' => false]);
        $this->expectException(ValidationException::class);
        app(CatalogPublishing::class)->submit($p);
    }

    public function test_review_rejection_preserves_feedback_and_allows_resubmission(): void
    {
        $p = $this->proposal();
        app(CatalogPublishing::class)->submit($p);
        app(CatalogPublishing::class)->reject($p->fresh(), 'Please improve specifications.');
        $this->assertSame('rejected', $p->fresh()->status);
        $p->refresh()->update(['description' => 'Improved original specifications.']);
        app(CatalogPublishing::class)->submit($p);
        $this->assertSame('pending', $p->fresh()->status);
    }

    public function test_variant_selection_is_snapshotted_at_checkout(): void
    {
        $variant = ProductVariant::create(['product_id' => $this->offers[0]->product_id, 'name' => 'Forest / Medium', 'code' => 'FOREST-M', 'attributes' => ['color' => 'Forest', 'size' => 'M']]);
        $this->offers[0]->update(['product_variant_id' => $variant->id]);
        $order = $this->place($this->cart(1, false));
        $this->assertSame('Forest / Medium', $order->items->first()->variant_snapshot['name']);
        $variant->update(['name' => 'Revised name']);
        $this->assertSame('Forest / Medium', $order->items->first()->fresh()->variant_snapshot['name']);
    }

    public function test_offer_cannot_use_a_variant_of_another_product(): void
    {
        $variant = ProductVariant::create(['product_id' => $this->offers[1]->product_id, 'name' => 'Other variant', 'code' => 'OTHER', 'attributes' => []]);
        $this->expectException(ValidationException::class);
        $this->offers[0]->update(['product_variant_id' => $variant->id]);
    }

    private function import(string $csv): CatalogImport
    {
        Storage::fake('local');
        $path = 'tenants/'.$this->tenant->id.'/imports/catalog.csv';
        Storage::disk('local')->put($path, $csv);

        return CatalogImport::create(['vendor_id' => $this->offers[0]->vendor_id, 'warehouse_id' => $this->stocks[0]->warehouse_id, 'user_id' => $this->customer->id, 'name' => 'Licensed CSV', 'path' => $path, 'rights_confirmed' => true]);
    }

    public function test_csv_validates_and_stages_without_automatically_publishing(): void
    {
        $import = $this->import("name,description,category_slug,sku,price_minor,quantity\nImported item,Original description,home-living,CSV-ONE,1200,4\n");
        app(CatalogImporter::class)->scan($import);
        $this->assertSame('review_ready', $import->fresh()->status);
        app(CatalogImporter::class)->stage($import);
        app(CatalogImporter::class)->stage($import);
        $this->assertSame(1, ProductProposal::count());
        $this->assertSame(2, Product::count());
        $this->assertSame('pending', ProductProposal::first()->status);
    }

    public function test_csv_errors_block_staging(): void
    {
        $import = $this->import("name,description,category_slug,sku,price_minor,quantity\nBad item,Description,missing-category,BAD,-1,4\n");
        app(CatalogImporter::class)->scan($import);
        $this->assertSame('needs_correction', $import->fresh()->status);
        $this->assertNotEmpty($import->fresh()->errors);
        $this->expectException(ValidationException::class);
        app(CatalogImporter::class)->stage($import);
    }
}
