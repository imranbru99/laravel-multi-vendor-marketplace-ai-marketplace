<?php

namespace App\Http\Controllers;

use App\Domain\CustomerReturns;
use App\Domain\ModuleManager;
use App\Domain\Subscriptions;
use App\Models\Address;
use App\Models\Booking;
use App\Models\CustomerNotification;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Models\Vendor;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ImranDev\UniversalSlug\Facades\UniversalSlug;

class AccountController extends Controller
{
    public function index()
    {
        return view('account.dashboard', ['orders' => Order::where('user_id', auth()->id())->latest()->paginate(10), 'wishlist' => Wishlist::where('user_id', auth()->id())->pluck('product_id'), 'addresses' => Address::where('user_id', auth()->id())->get(), 'tickets' => SupportTicket::where('user_id', auth()->id())->get(), 'quotations' => Quotation::with('offer.product')->where('user_id', auth()->id())->get(), 'bookings' => Booking::with('offer.product')->where('user_id', auth()->id())->get()]);
    }

    public function readNotification(int $id)
    {
        CustomerNotification::where('user_id', auth()->id())->findOrFail($id)->update(['read_at' => now()]);

        return back();
    }

    public function wishlist(Request $request, int $id)
    {
        Product::where('status', 'published')->findOrFail($id);
        $data = ['user_id' => auth()->id(), 'product_id' => $id];
        $existing = Wishlist::where($data)->first();
        $existing ? $existing->delete() : Wishlist::create($data);

        return back()->with('success', 'Wishlist updated.');
    }

    public function address(Request $request)
    {
        Address::create($request->validate(['name' => 'required|string|max:100', 'line1' => 'required|string|max:200', 'city' => 'required|string|max:100', 'postal_code' => 'required|string|max:20', 'country' => 'required|string|size:2']) + ['user_id' => auth()->id()]);

        return back()->with('success', 'Address saved.');
    }

    public function ticket(Request $request)
    {
        app(ModuleManager::class)->require('support');
        SupportTicket::create($request->validate(['subject' => 'required|string|max:150', 'message' => 'required|string|max:5000']) + ['user_id' => auth()->id()]);

        return back()->with('success', 'Support ticket opened.');
    }

    public function returnItem(Request $request, int $id)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:100', 'reason' => 'required|string|min:5|max:2000']);
        app(CustomerReturns::class)->request(auth()->id(), $id, (int) $data['quantity'], $data['reason']);

        return back()->with('success', 'Return requested. The team must receive the item and approve a sandbox refund.');
    }

    public function review(Request $request, int $id)
    {
        app(ModuleManager::class)->require('reviews');
        $data = $request->validate(['rating' => 'required|integer|min:1|max:5', 'title' => 'required|string|max:100', 'body' => 'required|string|min:10|max:2000']);
        $item = OrderItem::whereHas('order', fn ($q) => $q->where('user_id', auth()->id()))->whereHas('vendorOrder', fn ($q) => $q->where('status', 'completed'))->whereHas('offer', fn ($q) => $q->where('product_id', $id))->firstOrFail();
        Review::updateOrCreate(['user_id' => auth()->id(), 'product_id' => $id], $data + ['order_item_id' => $item->id]);

        return back()->with('success', 'Your verified purchase review was saved.');
    }

    public function quote(Request $request)
    {
        app(ModuleManager::class)->require('b2b');
        $data = $request->validate(['offer_id' => 'required|integer', 'quantity' => 'required|integer|min:10|max:100000', 'notes' => 'nullable|string|max:2000']);
        $offer = Offer::where('status', 'active')->whereHas('vendor', fn ($q) => $q->where('status', 'approved'))->findOrFail($data['offer_id']);
        app(ModuleManager::class)->transaction('b2b', fn () => Quotation::create($data + ['vendor_id' => $offer->vendor_id, 'user_id' => auth()->id()]), $offer->vendor_id);

        return redirect('/account')->with('success', 'Quotation requested.');
    }

    public function acceptQuote(int $id)
    {
        app(ModuleManager::class)->require('b2b');
        $quote = Quotation::where('user_id', auth()->id())->findOrFail($id);
        abort_unless($quote->status === 'quoted' && $quote->quoted_minor !== null, 422);
        $quote->update(['status' => 'accepted']);

        return back()->with('success', 'Quotation accepted. Payment terms must be arranged with the seller.');
    }

    public function book(Request $request)
    {
        app(ModuleManager::class)->require('bookings');
        $data = $request->validate(['offer_id' => 'required|integer', 'starts_at' => 'required|date|after:now']);
        app(ModuleManager::class)->transaction('bookings', function () use ($data) {
            $offer = Offer::where('status', 'active')->whereHas('product', fn ($q) => $q->where('type', 'service')->where('status', 'published'))->lockForUpdate()->findOrFail($data['offer_id']);
            $time = Carbon::parse($data['starts_at'])->startOfHour();
            abort_if(Booking::where('offer_id', $offer->id)->where('starts_at', $time)->exists(), 422, 'This hourly slot is already booked.');
            Booking::create(['offer_id' => $offer->id, 'vendor_id' => $offer->vendor_id, 'user_id' => auth()->id(), 'starts_at' => $time]);
        });

        return redirect('/account')->with('success', 'Service appointment booked.');
    }

    public function applyVendor(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'description' => 'required|string|max:2000']);
        app(ModuleManager::class)->transaction('catalog', function () use ($data) {
            app(Subscriptions::class)->quota('Vendor');
            Vendor::create($data + ['slug' => UniversalSlug::make($data['name']).'-'.Str::lower(Str::random(5)), 'email' => auth()->user()->email]);
        });

        return back()->with('success', 'Seller application submitted for review. An administrator must approve and assign your account.');
    }
}
