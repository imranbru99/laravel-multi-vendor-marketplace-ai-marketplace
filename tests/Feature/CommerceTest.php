<?php

namespace Tests\Feature;

use App\Domain\BusinessRules;
use App\Domain\Ledger;
use App\Domain\ModuleManager;
use App\Domain\Money;
use App\Domain\OrderWorkflow;
use App\Domain\SandboxGateway;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\InventoryMovement;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\Quotation;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class CommerceTest extends MarketplaceTestCase
{
    public function test_checkout_splits_vendors_and_snapshots_exact_totals(): void
    {
        $order = $this->place();
        $this->assertSame(20002, $order->subtotal_minor);
        $this->assertSame(1000, $order->tax_minor);
        $this->assertSame(21502, $order->total_minor);
        $this->assertCount(2, $order->vendorOrders);
        $this->assertSame(1000, $order->items[0]->commission_minor);
        $this->assertSame(1, $this->stocks[0]->fresh()->reserved);
        $this->offers[0]->update(['price_minor' => 99999]);
        $this->assertSame(10001, $order->items[0]->fresh()->unit_minor);
    }

    public function test_duplicate_checkout_key_does_not_duplicate_reservations(): void
    {
        $cart = $this->cart();
        $order = $this->place($cart, 'same-key');
        $again = $this->place($cart, 'same-key');
        $this->assertSame($order->id, $again->id);
        $this->assertSame(1, Order::count());
        $this->assertSame(1, $this->stocks[0]->fresh()->reserved);
    }

    public function test_overselling_rolls_back_every_vendor_and_order(): void
    {
        $this->place($this->cart(9));
        try {
            $this->place($this->cart(2));
            $this->fail('Oversold inventory');
        } catch (ValidationException) {
        }
        $this->assertSame(1, Order::count());
        $this->assertSame(9, $this->stocks[0]->fresh()->reserved);
        $this->assertSame(9, $this->stocks[1]->fresh()->reserved);
    }

    public function test_capture_is_idempotent_and_balances_ledger(): void
    {
        $order = $this->place();
        $this->capture($order);
        $this->capture($order);
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(9, $this->stocks[0]->fresh()->quantity);
        $this->assertSame(0, $this->stocks[0]->fresh()->reserved);
        $this->assertSame(1, LedgerTransaction::count());
        $this->assertSame(0, (int) LedgerEntry::sum('amount_minor'));
        $this->assertSame(1, PaymentEvent::count());
    }

    public function test_capture_rejects_mismatched_amount(): void
    {
        $order = $this->place();
        $p = $order->payments->first();
        $this->expectException(ValidationException::class);
        app(SandboxGateway::class)->confirm($p, 'bad', 1, 'USD');
    }

    public function test_cancellation_releases_stock_once(): void
    {
        $order = $this->place();
        app(OrderWorkflow::class)->cancel($order);
        app(OrderWorkflow::class)->cancel($order);
        $this->assertSame(0, $this->stocks[0]->fresh()->reserved);
        $this->assertSame(10, $this->stocks[0]->fresh()->quantity);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_expiration_releases_reservations(): void
    {
        $order = $this->place();
        $order->update(['expires_at' => now()->subMinute()]);
        $this->artisan('marketplace:expire-orders')->assertSuccessful();
        $this->context();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, $this->stocks[0]->fresh()->reserved);
    }

    public function test_captured_order_cannot_be_cancelled(): void
    {
        $order = $this->place();
        $this->capture($order);
        $this->expectException(ValidationException::class);
        app(OrderWorkflow::class)->cancel($order);
    }

    public function test_coupon_limit_is_enforced_and_discount_snapshotted(): void
    {
        Coupon::create(['code' => 'ONE', 'discount_bps' => 1000, 'usage_limit' => 1]);
        $order = $this->place(null, null, 'ONE');
        $this->assertSame(2000, $order->discount_minor);
        $this->assertSame(900, $order->tax_minor);
        $this->assertSame(900, $order->items[0]->commission_minor);
        $this->expectException(ValidationException::class);
        $this->place(null, null, 'ONE');
    }

    public function test_partial_refund_and_duplicate_are_balanced(): void
    {
        $order = $this->place($this->cart(3));
        $this->capture($order);
        $this->deliver($order);
        $item = $order->items()->first();
        $return = ReturnRequest::create(['vendor_id' => $item->vendor_id, 'order_item_id' => $item->id, 'user_id' => $this->customer->id, 'quantity' => 1, 'reason' => 'Test received item']);
        $one = app(OrderWorkflow::class)->refund($return);
        $again = app(OrderWorkflow::class)->refund($return);
        $this->assertSame($one->id, $again->id);
        $this->assertSame(1, Refund::count());
        $this->assertSame(0, (int) LedgerEntry::sum('amount_minor'));
        $two = ReturnRequest::create(['vendor_id' => $item->vendor_id, 'order_item_id' => $item->id, 'user_id' => $this->customer->id, 'quantity' => 2, 'reason' => 'Remaining received items']);
        app(OrderWorkflow::class)->refund($two);
        $this->assertSame($item->total_minor, (int) Refund::sum('amount_minor'));
        $this->assertSame($item->commission_minor, (int) Refund::sum('commission_minor'));
        $this->assertSame(10, $this->stocks[0]->fresh()->quantity);
        $this->assertSame(0, (int) LedgerEntry::sum('amount_minor'));
    }

    public function test_settlement_requires_return_window_then_posts_once(): void
    {
        $order = $this->place();
        $this->capture($order);
        $this->deliver($order);
        $sub = $order->vendorOrders()->first();
        try {
            app(OrderWorkflow::class)->settle($sub);
            $this->fail('Settled before return window');
        } catch (ValidationException) {
        }
        Shipment::where('vendor_order_id', $sub->id)->update(['delivered_at' => now()->subDays(15)]);
        $settlement = app(OrderWorkflow::class)->settle($sub);
        $again = app(OrderWorkflow::class)->settle($sub);
        $this->assertSame($settlement->id, $again->id);
        $this->assertSame($sub->payable_minor, $settlement->amount_minor);
        $this->assertSame(0, (int) LedgerEntry::sum('amount_minor'));
    }

    public function test_active_quotations_block_module_shutdown_and_history_survives(): void
    {
        $offer = $this->offers[0];
        $quote = Quotation::create(['user_id' => $this->customer->id, 'vendor_id' => $offer->vendor_id, 'offer_id' => $offer->id, 'quantity' => 10]);
        try {
            app(ModuleManager::class)->toggle('b2b', false);
            $this->fail('Disabled active module');
        } catch (ValidationException) {
        }
        $quote->update(['status' => 'closed']);
        app(ModuleManager::class)->toggle('b2b', false);
        $this->assertFalse(app(ModuleManager::class)->enabled('b2b'));
        $this->assertSame(1, Quotation::count());
        app(ModuleManager::class)->toggle('b2b', true);
        $this->assertSame(1, Quotation::count());
    }

    public function test_dependent_modules_block_deactivation(): void
    {
        $this->expectException(ValidationException::class);
        app(ModuleManager::class)->toggle('checkout', false);
    }

    public function test_core_module_cannot_be_disabled(): void
    {
        $this->expectException(ValidationException::class);
        app(ModuleManager::class)->toggle('catalog', false);
    }

    public function test_subscription_branch_limit_then_upgrade(): void
    {
        $data = ['vendor_id' => $this->offers[0]->vendor_id, 'name' => 'Third', 'address' => 'Example'];
        try {
            app(BusinessRules::class)->branch($data);
            $this->fail('Branch limit ignored');
        } catch (ValidationException) {
        }
        $this->tenant->update(['plan' => 'growth', 'branch_limit' => 25]);
        $branch = app(BusinessRules::class)->branch($data);
        $this->assertSame('Third', $branch->name);
        $this->assertSame(3, Branch::count());
    }

    public function test_inventory_history_is_immutable(): void
    {
        $movement = InventoryMovement::first();
        $this->expectException(\LogicException::class);
        $movement->update(['quantity_delta' => 999]);
    }

    public function test_unbalanced_journal_is_rejected(): void
    {
        $this->expectException(\LogicException::class);
        app(Ledger::class)->post('bad', 'Bad journal', 'USD', [['account' => 'cash', 'amount_minor' => 100], ['account' => 'payable', 'amount_minor' => -99]]);
    }

    public function test_negative_or_excessive_percentages_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::percentage(100, 10001);
    }

    public function test_integer_half_up_rounding(): void
    {
        $this->assertSame(1, Money::percentage(5, 1000));
        $this->assertSame(0, Money::percentage(4, 1000));
        $this->assertSame('USD 100.01', Money::format(10001));
    }
}
