<?php

namespace Tests\Feature;

use App\Domain\BusinessRules;
use App\Domain\Inventory;
use App\Filament\Resources\InventoryBalanceResource;
use App\Jobs\RecordOrderConfirmation;
use App\Models\CustomerNotification;
use App\Models\Employee;
use App\Models\InventoryBalance;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\TenantModule;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class ExtendedWorkflowsTest extends MarketplaceTestCase
{
    public function test_customer_addresses_wishlist_and_support_persist(): void
    {
        $this->actingAs($this->customer)->post('/addresses', ['name' => 'Test', 'line1' => '100 Example', 'city' => 'Demo', 'postal_code' => '1000', 'country' => 'US'])->assertRedirect();
        $this->post('/wishlist/'.$this->offers[0]->product_id)->assertRedirect();
        $this->post('/tickets', ['subject' => 'Question', 'message' => 'Please explain the delivery process.'])->assertRedirect();
        $this->assertDatabaseCount('addresses', 1);
        $this->assertDatabaseCount('wishlists', 1);
        $this->assertDatabaseHas('support_tickets', ['user_id' => $this->customer->id, 'status' => 'open']);
    }

    public function test_wholesale_request_and_customer_acceptance(): void
    {
        $this->actingAs($this->customer)->post('/quotations', ['offer_id' => $this->offers[0]->id, 'quantity' => 20, 'notes' => 'Team order'])->assertRedirect('/account');
        $this->context();
        $quote = Quotation::firstOrFail();
        $quote->update(['quoted_minor' => 150000, 'status' => 'quoted']);
        $this->post('/quotations/'.$quote->id.'/accept')->assertRedirect();
        $this->assertDatabaseHas('quotations', ['id' => $quote->id, 'status' => 'accepted']);
    }

    public function test_service_slot_cannot_be_double_booked(): void
    {
        $this->offers[0]->product->update(['type' => 'service']);
        $data = ['offer_id' => $this->offers[0]->id, 'starts_at' => now()->addDays(2)->startOfHour()->toDateTimeString()];
        $this->actingAs($this->customer)->post('/bookings', $data)->assertRedirect('/account');
        $this->post('/bookings', $data)->assertStatus(422);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_purchase_receipt_is_idempotent_and_updates_movement(): void
    {
        $offer = $this->offers[0];
        $purchase = PurchaseOrder::create(['vendor_id' => $offer->vendor_id, 'supplier' => 'Example supplier', 'offer_id' => $offer->id, 'inventory_balance_id' => $this->stocks[0]->id, 'quantity' => 5, 'unit_cost_minor' => 5000]);
        app(BusinessRules::class)->receive($purchase);
        app(BusinessRules::class)->receive($purchase);
        $this->assertSame(15, $this->stocks[0]->fresh()->quantity);
        $this->assertSame('received', $purchase->fresh()->status);
        $this->assertDatabaseCount('inventory_movements', 3);
    }

    public function test_transfer_preserves_total_stock_and_rejects_cross_vendor(): void
    {
        $stock = $this->stocks[0];
        $w = Warehouse::create(['vendor_id' => $stock->vendor_id, 'branch_id' => $stock->warehouse->branch_id, 'name' => 'Second location', 'location' => 'A2']);
        $destination = InventoryBalance::create(['vendor_id' => $stock->vendor_id, 'offer_id' => $stock->offer_id, 'warehouse_id' => $w->id]);
        app(Inventory::class)->transfer($stock, $destination, 3, 'transfer-test');
        $this->assertSame(7, $stock->fresh()->quantity);
        $this->assertSame(3, $destination->fresh()->quantity);
        $this->expectException(ValidationException::class);
        app(Inventory::class)->transfer($stock, $this->stocks[1], 1, 'bad-transfer');
    }

    public function test_notification_job_is_scoped_and_idempotent(): void
    {
        $order = $this->place();
        $job = new RecordOrderConfirmation($this->tenant->id, $order->id);
        $job->handle();
        $job->handle();
        $this->assertSame(1, CustomerNotification::count());
        $notification = CustomerNotification::first();
        $this->assertSame($this->customer->id, $notification->user_id);
        $this->actingAs($this->customer)->post('/notifications/'.$notification->id.'/read')->assertRedirect();
        $this->assertDatabaseMissing('customer_notifications', ['id' => $notification->id, 'read_at' => null]);
    }

    public function test_workforce_rejects_cross_vendor_branch_assignment(): void
    {
        $this->expectException(ValidationException::class);
        Employee::create(['vendor_id' => $this->offers[0]->vendor_id, 'branch_id' => $this->stocks[1]->warehouse->branch_id, 'name' => 'Example', 'email' => 'staff@example.test', 'job_title' => 'Assistant']);
    }

    public function test_disabled_module_blocks_inventory_action_in_livewire(): void
    {
        $this->actingAs($this->admin);
        $this->assertTrue(InventoryBalanceResource::canViewAny());
        // Direct setting models a panel remaining mounted after another admin disabled access.
        TenantModule::where('key', 'inventory')->update(['enabled' => false]);
        $this->assertFalse(InventoryBalanceResource::canViewAny());
    }
}
