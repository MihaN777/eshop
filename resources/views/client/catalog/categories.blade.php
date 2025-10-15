@extends('layouts.client-layout')

@section('title', 'Каталог')

@section('content')
    <section class="mt-16 lg:mt-24">
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 md:gap-5 mt-8">
            @each('client.catalog.shared.categories', $categories, 'category')
        </div>
    </section>
@endsection
