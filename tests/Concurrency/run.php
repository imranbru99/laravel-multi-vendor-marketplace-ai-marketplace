<?php

/** Standalone multi-process PostgreSQL verification. Only a *_concurrency database is allowed. */
use App\Domain\Checkout;
use App\Domain\Inventory;
use App\Domain\ModuleManager;
use App\Domain\OrderWorkflow;
use App\Domain\SandboxGateway;
use App\Domain\TenantContext;
use App\Domain\TenantSetup;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\InventoryBalance;
use App\Models\LedgerTransaction;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\Settlement;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Models\Warehouse;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::getDriverName() !== 'pgsql' || ! str_ends_with(config('database.connections.pgsql.database'), '_concurrency')) {
    fwrite(STDERR, "Requires isolated PostgreSQL database ending _concurrency.\n");
    exit(1);
}
config(['marketplace.sandbox' => true]);
function context(): void
{
    app(TenantContext::class)->set(Tenant::where('slug', 'concurrency')->firstOrFail());
}
function place(Cart $cart, ?string $coupon = null): Order
{
    return app(Checkout::class)->place($cart, ['email' => 'qa@example.test', 'address' => ['name' => 'QA', 'line1' => 'Example', 'city' => 'Demo', 'postal_code' => '1000', 'country' => 'US']], 'cart:'.$cart->id, $coupon);
}
if (($argv[1] ?? '') === 'worker') {
    try {
        context();
        [$action,$id,$extra] = [$argv[2], (int) $argv[3], $argv[4] ?? null];
        match ($action) {
            'checkout' => place(Cart::findOrFail($id), $extra),
            'capture' => (function () use ($id) {
                $p = Payment::findOrFail($id);
                app(SandboxGateway::class)->confirm($p, 'parallel:'.$p->id, $p->amount_minor, $p->currency);
            })(),
            'refund' => app(OrderWorkflow::class)->refund(ReturnRequest::findOrFail($id)),
            'settle' => app(OrderWorkflow::class)->settle(VendorOrder::findOrFail($id)),
            'cancel' => app(OrderWorkflow::class)->cancel(Order::findOrFail($id)),
            'transfer' => app(Inventory::class)->transfer(InventoryBalance::findOrFail($id), InventoryBalance::findOrFail((int) $extra), 1, 'parallel:'.Str::uuid()),
            'quote' => app(ModuleManager::class)->transaction('b2b', function () use ($id) {
                $offer = Offer::findOrFail($id);

                return Quotation::create(['user_id' => User::where('email', 'qa@example.test')->firstOrFail()->id, 'vendor_id' => $offer->vendor_id, 'offer_id' => $offer->id, 'quantity' => 10]);
            }),
            'disable' => app(ModuleManager::class)->toggle('b2b', false),
        };
        echo 'ok';
        exit(0);
    } catch (ValidationException|HttpException $e) {
        echo 'blocked';
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, get_class($e).': '.$e->getMessage());
        exit(1);
    }
}
function parallel(array $jobs): array
{
    $children = [];
    foreach ($jobs as $job) {
        $pipes = [];
        $process = proc_open(array_merge([PHP_BINARY, __FILE__, 'worker'], $job), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $children[] = [$process, $pipes];
    }
    $results = [];
    foreach ($children as [$process,$pipes]) {
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        if ($exit !== 0) {
            throw new RuntimeException('Worker failed: '.$err);
        } $results[] = $out;
    }

    return $results;
}
function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}
function fixture(int $units): array
{
    $v = Vendor::first();
    $w = Warehouse::first();
    $p = Product::create(['name' => 'Concurrent item', 'slug' => 'item-'.Str::uuid(), 'description' => 'Test', 'status' => 'published', 'category_id' => Category::first()->id]);
    $o = Offer::create(['vendor_id' => $v->id, 'product_id' => $p->id, 'sku' => (string) Str::uuid(), 'price_minor' => 10001]);
    $s = InventoryBalance::create(['vendor_id' => $v->id, 'offer_id' => $o->id, 'warehouse_id' => $w->id]);
    app(Inventory::class)->change($s, $units, 0, 'initial', 'init:'.$s->id);

    return [$o, $s];
}
function carts(Offer $offer, int $number, int $qty = 1): array
{
    $out = [];
    for ($i = 0; $i < $number; $i++) {
        $cart = Cart::create(['user_id' => User::where('email', 'qa@example.test')->first()->id, 'token' => (string) Str::uuid()]);
        CartItem::create(['cart_id' => $cart->id, 'offer_id' => $offer->id, 'quantity' => $qty]);
        $out[] = $cart;
    }

    return $out;
}
$tenant = Tenant::create(['name' => 'Concurrency QA', 'slug' => 'concurrency', 'branch_limit' => 25]);
context();
app(TenantSetup::class)->initialize($tenant);
$user = User::create(['tenant_id' => $tenant->id, 'name' => 'QA', 'email' => 'qa@example.test', 'password' => Str::random(40)]);
$category = Category::create(['name' => 'QA', 'slug' => 'qa']);
$vendor = Vendor::create(['name' => 'QA', 'slug' => 'qa', 'email' => 'vendor@example.test', 'status' => 'approved']);
$branch = Branch::create(['vendor_id' => $vendor->id, 'name' => 'QA', 'address' => 'QA']);
$warehouse = Warehouse::create(['vendor_id' => $vendor->id, 'branch_id' => $branch->id, 'name' => 'QA', 'location' => 'A1']);
$report = [];
[$offer,$stock] = fixture(1);
$carts = carts($offer, 8);
$results = parallel(array_map(fn ($c) => ['checkout', (string) $c->id], $carts));
check(count(array_filter($results, fn ($r) => $r === 'ok')) === 1, 'Last unit oversold');
check($stock->fresh()->reserved === 1, 'Reservation incorrect');
$report['last_unit_checkout'] = ['workers' => 8, 'successful' => 1, 'blocked' => 7];
$order = Order::first();
$payment = $order->payments->first();
parallel(array_fill(0, 8, ['capture', (string) $payment->id]));
check(LedgerTransaction::where('reference', 'capture:'.$payment->id)->count() === 1, 'Duplicate capture journal');
check($stock->fresh()->quantity === 0 && $stock->fresh()->reserved === 0, 'Capture stock incorrect');
$report['duplicate_capture'] = ['workers' => 8, 'capture_journals' => 1];
$sub = $order->vendorOrders->first();
$shipment = app(OrderWorkflow::class)->ship($sub, 'QA', 'QA');
app(OrderWorkflow::class)->deliver($shipment);
$item = $order->items->first();
$returns = [];
for ($i = 0; $i < 8; $i++) {
    $returns[] = ReturnRequest::create(['vendor_id' => $vendor->id, 'order_item_id' => $item->id, 'user_id' => $user->id, 'quantity' => 1, 'reason' => 'Concurrent test']);
}
$results = parallel(array_map(fn ($r) => ['refund', (string) $r->id], $returns));
check(Refund::where('order_id', $order->id)->count() === 1, 'Duplicate refund');
check($stock->fresh()->quantity === 1, 'Return stock incorrect');
$report['competing_refunds'] = ['workers' => 8, 'refunds' => 1, 'blocked' => 7];
[$offer,$stock] = fixture(5);
$order = place(carts($offer, 1)[0]);
$payment = $order->payments->first();
app(SandboxGateway::class)->confirm($payment, 'settle-capture', $payment->amount_minor, $payment->currency);
$sub = $order->vendorOrders->first();
$shipment = app(OrderWorkflow::class)->ship($sub, 'QA', 'QA-settle');
app(OrderWorkflow::class)->deliver($shipment);
$shipment->update(['delivered_at' => now()->subDays(15)]);
parallel(array_fill(0, 8, ['settle', (string) $sub->id]));
check(Settlement::where('vendor_order_id', $sub->id)->count() === 1, 'Duplicate settlement');
$report['duplicate_settlement'] = ['workers' => 8, 'settlements' => 1];
[$offer,$stock] = fixture(20);
Coupon::create(['code' => 'LAST', 'discount_bps' => 1000, 'usage_limit' => 1]);
$results = parallel(array_map(fn ($c) => ['checkout', (string) $c->id, 'LAST'], carts($offer, 8)));
check(count(array_filter($results, fn ($r) => $r === 'ok')) === 1, 'Coupon over-redeemed');
check(Coupon::where('code', 'LAST')->first()->used === 1, 'Coupon counter incorrect');
$report['last_coupon'] = ['workers' => 8, 'redemptions' => 1, 'blocked' => 7];
[$offer,$stock] = fixture(3);
$otherWarehouse = Warehouse::create(['vendor_id' => $vendor->id, 'branch_id' => $branch->id, 'name' => 'Other', 'location' => 'A2']);
$destination = InventoryBalance::create(['vendor_id' => $vendor->id, 'offer_id' => $offer->id, 'warehouse_id' => $otherWarehouse->id]);
$results = parallel(array_fill(0, 8, ['transfer', (string) $stock->id, (string) $destination->id]));
check(count(array_filter($results, fn ($r) => $r === 'ok')) === 3, 'Transfer counts incorrect');
check($stock->fresh()->quantity === 0 && $destination->fresh()->quantity === 3, 'Transfer stock lost');
$report['stock_transfers'] = ['workers' => 8, 'successful' => 3, 'blocked' => 5];
$results = parallel([['quote', (string) $offer->id], ['disable', '0']]);
$enabled = app(ModuleManager::class)->enabled('b2b');
$quoteCount = Quotation::count();
check(($enabled && $quoteCount === 1) || (! $enabled && $quoteCount === 0), 'Module shutdown raced with operation');
$report['module_shutdown'] = ['workers' => 2, 'enabled' => $enabled, 'active_quotes' => $quoteCount];
foreach (LedgerTransaction::all() as $tx) {
    check((int) $tx->entries()->sum('amount_minor') === 0, 'Unbalanced journal '.$tx->id);
}check(! InventoryBalance::whereRaw('reserved > quantity')->exists(), 'Invalid stock invariant');
$report['ledger_balanced'] = true;
$report['inventory_invariants'] = true;
echo json_encode($report, JSON_PRETTY_PRINT)."\n";
