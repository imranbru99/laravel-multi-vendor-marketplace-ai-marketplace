@extends('layouts.app')
@section('title',$vendor->name.' — Common Ground')
@section('content')
<div class="container"><div class="breadcrumb"><a href="/">Home</a> / Independent stores</div><div class="page-heading"><span class="eyebrow">MEET THE SELLER</span><h1>{{ $vendor->name }}</h1><p>{{ $vendor->description }}</p><p class="fine-print">Simulated business · Marketplace approved</p></div><div class="product-grid">@forelse($products as $product)<x-product-card :product="$product"/>@empty<p>This store is preparing its collection.</p>@endforelse</div><div class="pagination">{{ $products->links() }}</div></div>
@endsection
