@extends('layouts.client-layout')

@section('title', "Каталог: {$product->title}")

@section('content')
    <section class="mt-16 lg:mt-24">
        <div
            class="products grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-x-8 gap-y-8 lg:gap-y-10 2xl:gap-y-12 mt-8">
            @include('client.catalog.shared.product')
        </div>
    </section>
@endsection
