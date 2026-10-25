<?php

use App\Domain\OperationsAccess;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\StoreController;
use App\Models\ContentPage;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\SubscriptionInvoice;
use App\Models\VendorOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ImranDev\UnicodePdf\Facades\UnicodePdf;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::post('/locale', function (Request $request) {
    $data = $request->validate(['locale' => 'required|in:en,bn']);
    $request->session()->put('locale', $data['locale']);

    return back();
});
Route::get('/shop', [StoreController::class, 'browse'])->name('shop');
Route::get('/products/{slug}', [StoreController::class, 'product'])->name('product');
Route::get('/sellers/{slug}', [StoreController::class, 'seller']);
Route::get('/pages/{slug}', [StoreController::class, 'page']);
Route::view('/platform', 'marketing.platform');
Route::get('/demo', fn () => view('marketing.demo'))->name('demo');
Route::get('/login', fn () => view('account.auth', ['mode' => 'login']))->name('login');
Route::get('/register', fn () => view('account.auth', ['mode' => 'register']));
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::get('/forgot-password', fn () => view('account.password', ['reset' => false]))->name('password.request');
Route::post('/forgot-password', [PasswordController::class, 'request'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', fn (string $token) => view('account.password', ['reset' => true, 'token' => $token]))->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::post('/contact', [StoreController::class, 'contact'])->middleware('throttle:5,1');
Route::post('/newsletter', [StoreController::class, 'newsletter'])->middleware('throttle:5,1');
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'add'])->middleware('throttle:60,1');
Route::patch('/cart/{id}', [CartController::class, 'update']);
Route::get('/checkout', [CartController::class, 'checkout']);
Route::post('/checkout', [CartController::class, 'place'])->middleware('throttle:10,1');
Route::get('/orders/{number}', [CartController::class, 'order']);
Route::post('/orders/{number}/pay', [CartController::class, 'pay'])->middleware('throttle:10,1');
Route::post('/orders/{number}/cancel', [CartController::class, 'cancel']);
Route::get('/orders/{number}/invoice', [CartController::class, 'invoice']);
Route::middleware('auth')->group(function () {
    Route::get('/downloads/{id}', DownloadController::class)->middleware('signed')->name('digital.download');
    Route::post('/notifications/{id}/read', [AccountController::class, 'readNotification']);
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::post('/wishlist/{id}', [AccountController::class, 'wishlist']);
    Route::post('/addresses', [AccountController::class, 'address']);
    Route::post('/tickets', [AccountController::class, 'ticket'])->middleware('throttle:10,1');
    Route::post('/returns/{id}', [AccountController::class, 'returnItem']);
    Route::post('/reviews/{id}', [AccountController::class, 'review']);
    Route::post('/quotations', [AccountController::class, 'quote']);
    Route::post('/quotations/{id}/accept', [AccountController::class, 'acceptQuote']);
    Route::post('/bookings', [AccountController::class, 'book']);
    Route::post('/sell', [AccountController::class, 'applyVendor'])->middleware('throttle:3,1');
});
Route::get('/sitemap.xml', function () {
    $urls = array_merge([url('/'), url('/shop'), url('/platform')], Product::where('status', 'published')->get()->map(fn ($p) => url('/products/'.$p->slug))->all(), ContentPage::pluck('slug')->map(fn ($s) => url('/pages/'.$s))->all());

    return response(view('marketing.sitemap', compact('urls')), 200, ['Content-Type' => 'application/xml']);
});
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /tenant\nDisallow: /vendor\nDisallow: /branch\nDisallow: /account\nDisallow: /orders\nSitemap: ".url('/sitemap.xml'), 200, ['Content-Type' => 'text/plain']));

Route::get('/billing/invoices/{id}', function (int $id) {
    abort_unless(in_array(auth()->user()?->role, ['superadmin', 'owner', 'admin'], true), 403);
    $invoice = SubscriptionInvoice::findOrFail($id);

    return UnicodePdf::view('account.subscription-invoice', compact('invoice'))->download('subscription-'.$invoice->id.'.pdf');
})->middleware('auth')->name('billing.invoice');

Route::get('/operations/shipments/{id}/packing-slip', function (int $id) {
    $shipment = OperationsAccess::scope(Shipment::query(), 'Shipment', auth()->user())->with('items.orderItem')->findOrFail($id);
    $sub = VendorOrder::with('order', 'vendor')->findOrFail($shipment->vendor_order_id);

    return UnicodePdf::view('account.packing-slip', compact('shipment', 'sub'))->download('packing-slip-'.$shipment->id.'.pdf');
})->middleware('auth')->name('shipment.packing-slip');
