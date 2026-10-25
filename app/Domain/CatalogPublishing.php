<?php

namespace App\Domain;

use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductProposal;
use App\Models\SlugRedirect;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ImranDev\UniversalSlug\Facades\UniversalSlug;

final class CatalogPublishing
{
    public function submit(ProductProposal $proposal): void
    {
        app(ModuleManager::class)->transaction('catalog', function () use ($proposal) {
            $proposal = ProductProposal::whereKey($proposal->id)->lockForUpdate()->firstOrFail();
            if (! in_array($proposal->status, ['draft', 'rejected']) || ! $proposal->rights_confirmed) {
                throw ValidationException::withMessages(['proposal' => 'Confirm distribution rights before submitting a draft.']);
            }$proposal->update(['status' => 'pending', 'review_notes' => null]);
            Audit::record('catalog.proposal_submitted', (string) $proposal->id);
        }, $proposal->vendor_id);
    }

    public function approve(ProductProposal $proposal): Product
    {
        if (auth()->check()) {
            abort_unless(app(AccessManager::class)->allows(auth()->user(), 'catalog.approve', in_array(auth()->user()->role, ['admin'], true)), 403);
        }

        return app(ModuleManager::class)->transaction('catalog', function () use ($proposal) {
            $proposal = ProductProposal::with('vendor')->whereKey($proposal->id)->lockForUpdate()->firstOrFail();
            if ($proposal->status === 'approved') {
                return Product::findOrFail($proposal->product_id);
            }if ($proposal->status !== 'pending' || ! $proposal->rights_confirmed || $proposal->vendor->status !== 'approved') {
                throw ValidationException::withMessages(['proposal' => 'A reviewed submission from an approved seller is required.']);
            }
            if (Offer::where('sku', $proposal->sku)->exists()) {
                throw ValidationException::withMessages(['sku' => 'This marketplace SKU is already in use.']);
            }app(Subscriptions::class)->quota('Product');
            $warehouse = Warehouse::where('vendor_id', $proposal->vendor_id)->findOrFail($proposal->warehouse_id);
            $slug = UniversalSlug::make($proposal->name) ?: 'product';
            $base = $slug;
            $suffix = 1;
            while (Product::where('slug', $slug)->exists() || SlugRedirect::where('entity', 'Product')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.++$suffix;
            }
            $product = Product::create(['name' => $proposal->name, 'slug' => $slug, 'description' => $proposal->description, 'category_id' => $proposal->category_id, 'brand_id' => $proposal->brand_id, 'status' => 'published']);
            if ($proposal->image) {
                $mime = Storage::disk('local')->mimeType($proposal->image);
                $extension = match ($mime) {
                    'image/jpeg' => 'jpg','image/png' => 'png','image/webp' => 'webp',default => throw ValidationException::withMessages(['image' => 'Unsupported product image.'])
                };
                $path = 'tenants/'.app(TenantContext::class)->id().'/catalog/'.Str::uuid().'.'.$extension;
                Storage::disk('public')->put($path, Storage::disk('local')->get($proposal->image));
                $product->update(['image' => Storage::disk('public')->url($path)]);
            }
            $offer = Offer::create(['vendor_id' => $proposal->vendor_id, 'product_id' => $product->id, 'sku' => $proposal->sku, 'price_minor' => $proposal->price_minor]);
            $stock = InventoryBalance::create(['vendor_id' => $offer->vendor_id, 'offer_id' => $offer->id, 'warehouse_id' => $warehouse->id]);
            if ($proposal->initial_quantity) {
                app(Inventory::class)->change($stock, $proposal->initial_quantity, 0, 'approved seller catalog receipt', 'proposal:'.$proposal->id);
            }
            $proposal->update(['status' => 'approved', 'product_id' => $product->id]);
            Audit::record('catalog.proposal_approved', (string) $proposal->id);

            return $product;
        }, $proposal->vendor_id);
    }

    public function reject(ProductProposal $proposal, string $notes): void
    {
        if (auth()->check()) {
            abort_unless(app(AccessManager::class)->allows(auth()->user(), 'catalog.approve', auth()->user()->role === 'admin'), 403);
        }if ($proposal->status !== 'pending') {
            throw ValidationException::withMessages(['proposal' => 'Only submitted proposals can be rejected.']);
        }$proposal->update(['status' => 'rejected', 'review_notes' => $notes]);
        Audit::record('catalog.proposal_rejected',(string) $proposal->id);
    }
}
