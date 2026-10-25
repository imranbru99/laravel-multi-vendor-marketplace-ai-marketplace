<?php

namespace Tests\Feature;

use App\Filament\ResourcePages\ManageProductProposal;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\MarketplaceTestCase;

class SellerCatalogFormTest extends MarketplaceTestCase
{
    public function test_vendor_can_load_shared_categories_and_create_only_own_proposal(): void
    {
        $vendor = User::create(['name' => 'Seller', 'email' => 'catalogseller@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $this->offers[0]->vendor_id, 'role' => 'vendor']);
        $this->actingAs($vendor);
        Filament::setCurrentPanel(Filament::getPanel('vendor'));
        Livewire::test(ManageProductProposal::class)->callAction('create', data: ['vendor_id' => $vendor->vendor_id, 'warehouse_id' => $this->stocks[0]->warehouse_id, 'category_id' => $this->offers[0]->product->category_id, 'name' => 'Seller form item', 'description' => 'Original seller catalog description', 'sku' => 'FORM-SELLER-1', 'price_minor' => 2500, 'initial_quantity' => 5, 'rights_confirmed' => true])->assertHasNoActionErrors();
        $this->assertDatabaseHas('product_proposals', ['vendor_id' => $vendor->vendor_id, 'sku' => 'FORM-SELLER-1']);
    }
}
