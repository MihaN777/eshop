@extends('layouts.client-layout')

@section('title', "Каталог: {$category->title}")

@section('content')
    <section class="mt-16 lg:mt-24">
        <div
            class="products grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-x-8 gap-y-8 lg:gap-y-10 2xl:gap-y-12 mt-8">
            @each('client.catalog.shared.product', $products, 'product')
        </div>
    </section>
@endsection
