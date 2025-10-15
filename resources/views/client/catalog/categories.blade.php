@extends('layouts.client-layout')

@section('title', 'Каталог')

@section('content')
    <section class="mt-16 lg:mt-24">
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 md:gap-5 mt-8">
            @foreach($categories as $category)
                <a href="{{ route('catalog.category.products', $category->id) }}"
                   class="p-3 sm:p-4 2xl:p-6 rounded-xl bg-card hover:bg-pink text-xxs sm:text-xs lg:text-sm text-white font-semibold">
                    {{ $category->title }}
                </a>
            @endforeach
        </div>
    </section>
@endsection
