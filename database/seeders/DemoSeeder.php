<?php

namespace Database\Seeders;

use App\Domain\Checkout;
use App\Domain\Inventory;
use App\Domain\OrderWorkflow;
use App\Domain\SandboxGateway;
use App\Domain\TenantContext;
use App\Domain\TenantSetup;
use App\Models\AiDraft;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Employee;
use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ImranDev\UniversalSlug\Facades\UniversalSlug;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing') || ! config('marketplace.demo') || ! config('marketplace.sandbox')) {
            throw new \RuntimeException('Demo seeding requires local/testing, MARKETPLACE_DEMO=true and PAYMENT_SANDBOX=true.');
        }
        if (Tenant::whereIn('slug', ['atlas', 'harbor'])->exists()) {
            throw new \RuntimeException('Demo tenants already exist. Existing data is preserved. Use a separate empty demo database.');
        }
        DB::transaction(function () {
            $password = Hash::make('Demo@12345');
            $categories = ['Home & living' => 'home-living', 'Electronics' => 'electronics', 'Style & everyday' => 'fashion', 'Wellness' => 'beauty-wellness', 'Books & stationery' => 'books-stationery', 'Kitchen & dining' => 'kitchen-dining', 'Outdoor' => 'outdoor', 'Gifts' => 'gifts', 'Workspace' => 'workspace', 'Services' => 'services'];
            $catalog = [
                ['The everyday lounge chair', 'chair', 'home-living', 18900, '#e9e5db', 'A warm oak frame and a generously curved seat. An unhurried corner for your everyday rituals.'],
                ['Form wireless headphones', 'headphones', 'electronics', 12900, '#e5e9df', 'A considered audio companion with soft ear cushions and a simple, tactile silhouette.'],
                ['The market day tote', 'bag', 'fashion', 4800, '#eee7db', 'Room for the everyday. A structured carryall inspired by slow mornings and market-day discoveries.'],
                ['Sculpture ceramic vase', 'vase', 'home-living', 3600, '#e8e9df', 'An organic silhouette in a muted natural glaze. Made for a few stems, or a quiet moment on its own.'],
                ['Pleat table light', 'lamp', 'home-living', 7900, '#eee7d8', 'Soft light, thoughtful form. A pleated shade brings a little warmth to your reading corner.'],
                ['The essential timepiece', 'watch', 'fashion', 11500, '#e7e8df', 'A clean dial, warm leather strap, and considered proportions for every day.'],
                ['Slow morning kettle', 'kettle', 'kitchen-dining', 6500, '#e8e8dd', 'A sculptural pour and a comfortable handle. A simple invitation to take your time.'],
                ['Studio bookshelf speaker', 'speaker', 'electronics', 14900, '#e9e3d8', 'Warm materials meet a compact profile. A considered addition to your listening space.'],
                ['A field guide to less', 'book', 'books-stationery', 2400, '#e6e8df', 'A beautifully bound collection of ideas on making room for the things that matter.'],
                ['The daily hydration bottle', 'bottle', 'beauty-wellness', 2800, '#e4e9dd', 'A reusable companion in a softly muted finish. An everyday essential, thoughtfully considered.'],
                ['The focused workspace', 'desk', 'workspace', 22900, '#e8e5db', 'Clean lines and a warm natural surface. A calm foundation for the work you care about.'],
                ['Weekend canvas sneaker', 'shoe', 'fashion', 6900, '#eae6db', 'A relaxed everyday shape with a neutral palette. Ready for a little wandering.'],
            ];
            for ($t = 0; $t < 2; $t++) {
                $tenant = Tenant::create(['name' => $t ? 'Harbor Market (Simulated)' : 'Common Ground (Simulated)', 'slug' => $t ? 'harbor' : 'atlas', 'plan' => 'growth', 'branch_limit' => 25, 'timezone' => 'Asia/Dhaka', 'tax_bps' => 500]);
                app(TenantContext::class)->set($tenant);
                app(TenantSetup::class)->initialize($tenant);
                $cats = [];
                foreach ($categories as $name => $slug) {
                    $cats[$slug] = Category::create(compact('name', 'slug'));
                }
                $brands = [];
                for ($i = 0; $i < 15; $i++) {
                    $brands[] = Brand::create(['name' => ['Form & Field', 'Kindred Studio', 'Objects of Use', 'Northline', 'Quiet Hours'][$i % 5].' '.($i + 1), 'slug' => 'brand-'.($i + 1)]);
                }
                $vendors = [];
                $warehouses = [];
                $branches = [];
                for ($v = 0; $v < ($t ? 2 : 3); $v++) {
                    $vendor = Vendor::create(['name' => ($t ? ['Harbor Supply', 'Coastal Works'] : ['Form & Field', 'The Everyday Co.', 'Kindred Studio'])[$v], 'slug' => 'store-'.($v + 1), 'email' => 'seller'.($v + 1).'@'.($t ? 'harbor' : 'atlas').'.test', 'status' => 'approved', 'description' => 'A simulated independent store with a considered approach to everyday essentials. All business data is demonstration content.']);
                    $vendors[] = $vendor;
                    for ($b = 0; $b < ($t ? 3 : 2); $b++) {
                        $branch = Branch::create(['vendor_id' => $vendor->id, 'name' => $vendor->name.' · '.['Central', 'North', 'South'][$b], 'address' => 'Simulated business district '.($b + 1)]);
                        $branches[] = $branch;
                        if ($b < ($t ? 2 : ($v === 0 ? 2 : 1))) {
                            $warehouses[] = Warehouse::create(['vendor_id' => $vendor->id, 'branch_id' => $branch->id, 'name' => $branch->name.' warehouse', 'location' => 'Zone A / Rack 01']);
                        }
                    }
                }
                $roles = $t ? ['owner2' => 'owner'] : ['superadmin' => 'superadmin', 'owner' => 'owner', 'admin' => 'admin', 'vendor1' => 'vendor', 'vendor2' => 'vendor', 'manager' => 'manager', 'branch' => 'branch', 'warehouse' => 'warehouse', 'finance' => 'finance', 'support' => 'support', 'customer1' => 'customer', 'customer2' => 'customer', 'business' => 'business'];
                $users = [];
                foreach ($roles as $key => $role) {
                    $users[$key] = User::create(['name' => ucfirst($key).' (Demo)', 'email' => $key.'@demo.test', 'password' => $password, 'tenant_id' => $tenant->id, 'role' => $role, 'vendor_id' => in_array($role, ['vendor', 'manager', 'branch', 'warehouse']) ? $vendors[$key === 'vendor2' ? 1 : 0]->id : null, 'branch_id' => in_array($role, ['branch', 'warehouse']) ? $branches[0]->id : null]);
                }
                $customers = array_values(array_filter($users, fn ($u) => in_array($u->role, ['customer', 'business'])));
                for ($i = count($customers); $i < 50; $i++) {
                    $customers[] = User::create(['name' => 'Sample Shopper '.($i + 1), 'email' => 'shopper'.($i + 1).'@'.($t ? 'harbor' : 'atlas').'.test', 'password' => $password, 'tenant_id' => $tenant->id, 'role' => 'customer']);
                }
                $products = [];
                $offers = [];
                // First eight records form the editorial homepage collection.
                for ($i = 0; $i < 100; $i++) {
                    [$name,$art,$category,$price,$accent,$description] = $catalog[$i % count($catalog)];
                    $suffix = $i < 12 ? '' : ' · Edition '.(intdiv($i, 12) + 1);
                    $product = Product::create(['name' => $name.$suffix, 'slug' => UniversalSlug::make($name.$suffix).'-'.($i + 1), 'category_id' => $cats[$category]->id, 'brand_id' => $brands[$i % 15]->id, 'description' => $description.' Simulated product; illustration is representative only.', 'status' => 'published', 'image' => '/images/'.$art.'.svg', 'accent' => $accent, 'specifications' => ['Collection' => 'Everyday essentials', 'Dataset' => 'Simulated demonstration']]);
                    $products[] = $product;
                    $vendor = $vendors[$i % count($vendors)];
                    $offer = Offer::create(['vendor_id' => $vendor->id, 'product_id' => $product->id, 'sku' => strtoupper($tenant->slug).'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT), 'price_minor' => $price, 'compare_minor' => $i % 4 === 0 ? $price + 2000 : null]);
                    $offers[] = $offer;
                    $warehouse = collect($warehouses)->firstWhere('vendor_id', $vendor->id);
                    $stock = InventoryBalance::create(['vendor_id' => $vendor->id, 'offer_id' => $offer->id, 'warehouse_id' => $warehouse->id]);
                    app(Inventory::class)->change($stock, $i % 20 === 0 ? 8 : 60, 0, 'demo initial receipt', 'demo:stock:'.$stock->id);
                    if ($i < 50) {
                        $other = $vendors[($i + 1) % count($vendors)];
                        $additional = Offer::create(['vendor_id' => $other->id, 'product_id' => $product->id, 'sku' => strtoupper($tenant->slug).'-ALT-'.$i, 'price_minor' => $price + 500]);
                        $otherStock = InventoryBalance::create(['vendor_id' => $other->id, 'offer_id' => $additional->id, 'warehouse_id' => collect($warehouses)->firstWhere('vendor_id', $other->id)->id]);
                        app(Inventory::class)->change($otherStock, 30, 0, 'demo initial receipt', 'demo:stock:'.$otherStock->id);
                    }
                }
                foreach (range(0, $t ? 11 : 12) as $i) {
                    Coupon::create(['code' => 'FIND'.($i + 1), 'discount_bps' => 500 + ($i % 3) * 250, 'usage_limit' => 100, 'expires_at' => now()->addMonths(3)]);
                }
                for ($i = 0; $i < 5; $i++) {
                    Campaign::create(['name' => 'Simulated collection '.($i + 1), 'content' => 'Draft editorial collection for review. No messages have been delivered.']);
                }
                for ($i = 0; $i < 125; $i++) {
                    $customer = $customers[$i % 50];
                    $cart = Cart::create(['user_id' => $customer->id, 'token' => (string) Str::uuid()]);
                    foreach ([$offers[$i % 100], $offers[($i + 1) % 100]] as $offer) {
                        CartItem::create(['cart_id' => $cart->id, 'offer_id' => $offer->id, 'quantity' => 2]);
                    }
                    $order = app(Checkout::class)->place($cart, ['email' => $customer->email, 'address' => ['name' => $customer->name, 'line1' => '100 Example Avenue', 'city' => 'Demo City', 'postal_code' => '10000', 'country' => 'US']], 'demo:'.$tenant->id.':'.$i);
                    if ($i < 110) {
                        $payment = $order->payments()->first();
                        app(SandboxGateway::class)->confirm($payment, 'demo:capture:'.$payment->id, $payment->amount_minor, $payment->currency);
                    }
                    if ($i < 90) {
                        foreach ($order->vendorOrders as $sub) {
                            $shipment = app(OrderWorkflow::class)->ship($sub, 'Manual demo carrier', 'DEMO-'.$order->id.'-'.$sub->id);
                            if ($i < 80) {
                                app(OrderWorkflow::class)->deliver($shipment);
                                if ($i >= 30) {
                                    $shipment->update(['delivered_at' => now()->subDays(20)]);
                                }
                            }
                        }
                    }
                    if ($i < 15) {
                        $item = $order->items()->first();
                        $return = ReturnRequest::create(['vendor_id' => $item->vendor_id, 'order_item_id' => $item->id, 'user_id' => $customer->id, 'quantity' => 1, 'reason' => 'Simulated return: product did not fit the intended space.']);
                        app(OrderWorkflow::class)->refund($return);
                    }
                    if ($i >= 15 && $i < 25) {
                        $item = $order->items()->first();
                        ReturnRequest::create(['vendor_id' => $item->vendor_id, 'order_item_id' => $item->id, 'user_id' => $customer->id, 'quantity' => 1, 'reason' => 'Simulated return awaiting receipt and review.']);
                    }
                    if ($i >= 30 && $i < 40) {
                        foreach ($order->vendorOrders as $sub) {
                            app(OrderWorkflow::class)->settle($sub);
                        }
                    }
                    if ($i < 50) {
                        $item = $order->items()->first();
                        Review::create(['user_id' => $customer->id, 'product_id' => $item->offer->product_id, 'order_item_id' => $item->id, 'rating' => 4 + ($i % 2), 'title' => 'Sample review — considered everyday design', 'body' => 'This is a simulated verified-purchase review for demonstration. It is not a real customer endorsement.']);
                    }
                }
                foreach (array_slice($customers, 0, 5) as $index => $customer) {
                    SupportTicket::create(['user_id' => $customer->id, 'subject' => 'Demo order question '.($index + 1), 'message' => 'Simulated customer support conversation. Please explain how to track this example order.']);
                }
                $first = $offers[0];
                Quotation::create(['user_id' => $customers[0]->id, 'vendor_id' => $first->vendor_id, 'offer_id' => $first->id, 'quantity' => 20, 'notes' => 'Simulated corporate purchase inquiry.']);
                $employee = Employee::create(['vendor_id' => $vendors[0]->id, 'branch_id' => $branches[0]->id, 'name' => 'Demo Warehouse Associate', 'email' => 'staff@'.$tenant->slug.'.test', 'job_title' => 'Warehouse associate']);
                AttendanceRecord::create(['vendor_id' => $vendors[0]->id, 'employee_id' => $employee->id, 'clocked_in_at' => now()->startOfDay()->addHours(9), 'clocked_out_at' => now()->startOfDay()->addHours(17), 'source' => 'manual demo']);
                $stock = InventoryBalance::where('offer_id', $first->id)->first();
                PurchaseOrder::create(['vendor_id' => $first->vendor_id, 'supplier' => 'Simulated Supplier Ltd.', 'offer_id' => $first->id, 'inventory_balance_id' => $stock->id, 'quantity' => 20, 'unit_cost_minor' => 12000]);
                AiDraft::create(['product_id' => $products[0]->id, 'user_id' => $users[$t ? 'owner2' : 'owner']->id, 'content' => 'Sandbox suggestion: A comfortable everyday chair with warm materials and considered proportions. Human review required.']);
                $this->call(ContentSeeder::class);
                $this->call(DigitalDemoSeeder::class);
                $this->call(DemoSubscriptionSeeder::class);
            }
            app(TenantContext::class)->set(Tenant::where('slug', 'atlas')->firstOrFail());
        });
    }
}
