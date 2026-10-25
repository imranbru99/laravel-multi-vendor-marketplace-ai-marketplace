<?php

namespace App\Http\Controllers;

use App\Domain\ModuleManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\ContentPage;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Models\SlugRedirect;
use App\Models\Vendor;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function home()
    {
        return view('store.home', ['products' => Product::with(['offers.vendor', 'category'])->where('status', 'published')->whereHas('offers', fn ($q) => $q->where('status', 'active')->whereHas('vendor', fn ($v) => $v->where('status', 'approved')))->orderBy('id')->limit(8)->get(), 'categories' => Category::orderBy('id')->limit(6)->get()]);
    }

    public function browse(Request $request)
    {
        app(ModuleManager::class)->require('catalog');
        $data = $request->validate(['q' => 'nullable|string|max:100', 'category' => 'nullable|string|max:100', 'brand' => 'nullable|string|max:100', 'seller' => 'nullable|string|max:100', 'sort' => 'nullable|in:newest,price-asc,price-desc', 'min' => 'nullable|integer|min:0|max:100000000', 'max' => 'nullable|integer|min:0|max:100000000']);
        $query = Product::with(['offers' => fn ($q) => $q->where('status', 'active')->whereHas('vendor', fn ($v) => $v->where('status', 'approved'))->with('vendor'), 'category', 'brand'])->where('status', 'published');
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$data['q'].'%')->orWhere('description', 'like', '%'.$data['q'].'%'));
        }
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $data['category']));
        }
        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $data['brand']));
        }
        $query->whereHas('offers', function ($q) use ($request, $data) {
            $q->where('status', 'active')->whereHas('vendor', fn ($v) => $v->where('status', 'approved'));
            if ($request->filled('seller')) {
                $q->whereHas('vendor', fn ($v) => $v->where('slug', $data['seller']));
            } if ($request->filled('min')) {
                $q->where('price_minor', '>=', $data['min'] * 100);
            } if ($request->filled('max')) {
                $q->where('price_minor', '<=', $data['max'] * 100);
            }
        });
        if (in_array($request->input('sort'), ['price-asc', 'price-desc'])) {
            $query->withMin(['offers' => fn ($q) => $q->where('status', 'active')->whereHas('vendor', fn ($v) => $v->where('status', 'approved'))], 'price_minor')->orderBy('offers_min_price_minor', $request->input('sort') === 'price-asc' ? 'asc' : 'desc');
        } else {
            $query->latest('id');
        }

        return view('store.browse', ['products' => $query->paginate(12)->withQueryString(), 'categories' => Category::get(), 'brands' => Brand::get(), 'vendors' => Vendor::where('status', 'approved')->get()]);
    }

    public function product(string $slug)
    {
        if ($redirect = $this->historical('Product', $slug, '/products/', ['status' => 'published'])) {
            return $redirect;
        }
        $product = Product::with(['category', 'brand', 'offers' => fn ($q) => $q->where('status', 'active')->whereHas('vendor', fn ($v) => $v->where('status', 'approved'))->with('vendor', 'inventory'), 'reviews.user'])->where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('store.product', compact('product'));
    }

    public function seller(string $slug)
    {
        if ($redirect = $this->historical('Vendor', $slug, '/sellers/', ['status' => 'approved'])) {
            return $redirect;
        }

        return view('store.seller', ['vendor' => Vendor::where('slug', $slug)->where('status', 'approved')->firstOrFail(), 'products' => Product::with('offers.vendor', 'category')->where('status', 'published')->whereHas('offers', fn ($q) => $q->where('status', 'active')->whereHas('vendor', fn ($q) => $q->where('slug', $slug)->where('status', 'approved')))->paginate(12)]);
    }

    public function page(string $slug)
    {
        if ($redirect = $this->historical('ContentPage', $slug, '/pages/')) {
            return $redirect;
        }

        return view('marketing.page', ['page' => ContentPage::where('slug', $slug)->firstOrFail()]);
    }

    private function historical(string $entity, string $slug, string $prefix, array $conditions = [])
    {
        $class = 'App\\Models\\'.$entity;
        if ($class::where('slug', $slug)->where($conditions)->exists()) {
            return null;
        }
        $history = SlugRedirect::where('entity', $entity)->where('slug', $slug)->first();
        if (! $history) {
            return null;
        }
        $record = $class::where($conditions)->find($history->record_id);

        return $record && $record->slug !== $slug ? redirect($prefix.rawurlencode($record->slug), 301) : null;
    }

    public function contact(Request $request)
    {
        ContactMessage::create($request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255', 'subject' => 'required|string|max:150', 'message' => 'required|string|max:5000']));

        return back()->with('success', 'Your message has been saved. The marketplace team can read it in their dashboard.');
    }

    public function newsletter(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255']);
        NewsletterSubscriber::firstOrCreate($data);

        return back()->with('success', 'You are subscribed. Delivery is subject to the marketplace email configuration.');
    }
}
