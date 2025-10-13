@extends('layouts.client-layout')

@section('content')
    <section>
        <!-- Section heading -->
        <h2 class="text-lg lg:text-[42px] font-black">Наши преимущества</h2>

        <!-- Advantages -->
        <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mt-8">
            <div
                class="flex flex-col items-center gap-2 py-6 md:py-8 px-4 md:px-6 rounded-xl bg-purple text-white text-center">
                <svg class="w-10 md:w-12 h-10 md:h-12 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                     viewBox="0 0 18 18">
                    <path fill-rule="evenodd"
                          d="M1.302 2.65H11.64c.292 0 .528.236.528.527v1.056h3.007c.33 0 .62.218.71.536l.905 3.166c.106.019.205.07.282.147l.747.746a.528.528 0 0 1 .154.373v3.476a.528.528 0 0 1-.527.528H15.86a2.111 2.111 0 0 1-4.222 0H6.361a2.111 2.111 0 0 1-4.222 0H.556a.528.528 0 0 1-.528-.528V3.924a.53.53 0 0 1 .155-.374l.746-.746a.528.528 0 0 1 .373-.155ZM15.69 7.926l-.754-2.639h-2.768V7.4c0 .292.236.528.528.528h2.994Zm-1.94 4.224a1.056 1.056 0 1 1 0 2.111 1.056 1.056 0 0 1 0-2.11ZM3.195 13.205a1.055 1.055 0 1 0 2.11 0 1.055 1.055 0 0 0-2.11 0Z"
                          clip-rule="evenodd"/>
                </svg>
                <h5 class="pt-2 md:pt-4 text-sm md:text-md font-bold">Бесплатная доставка</h5>
                <div class="text-xs md:text-sm">от 1000 рублей</div>
            </div>
            <div
                class="flex flex-col items-center gap-2 py-6 md:py-8 px-4 md:px-6 rounded-xl bg-purple text-white text-center">
                <svg class="w-10 md:w-12 h-10 md:h-12 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                     viewBox="0 0 18 18">
                    <path
                        d="M9 0a3.6 3.6 0 0 0-3.6 3.6v.6h-.498A1.962 1.962 0 0 0 2.94 6.072l-.474 9.87A1.968 1.968 0 0 0 4.428 18h9.144a1.969 1.969 0 0 0 1.962-2.058l-.474-9.87A1.961 1.961 0 0 0 13.098 4.2H12.6v-.6A3.6 3.6 0 0 0 9 0ZM6.6 3.6a2.4 2.4 0 1 1 4.8 0v.6H6.6v-.6Zm-.984 3.114a.6.6 0 1 1 1.2 0 .6.6 0 0 1-1.2 0Zm5.568 0a.6.6 0 1 1 1.2 0 .6.6 0 0 1-1.2 0Z"/>
                </svg>
                <h5 class="pt-2 md:pt-4 text-sm md:text-md font-bold">Безопасный шопинг</h5>
                <div class="text-xs md:text-sm">100% гарантии</div>
            </div>
            <div
                class="flex flex-col items-center gap-2 py-6 md:py-8 px-4 md:px-6 rounded-xl bg-purple text-white text-center">
                <svg class="w-10 md:w-12 h-10 md:h-12 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                     viewBox="0 0 18 18">
                    <path
                        d="M17.953 6.905a.955.955 0 0 0-.823-.658l-5.196-.471L9.88.967a.957.957 0 0 0-1.76.001L6.066 5.776.87 6.247a.957.957 0 0 0-.543 1.674l3.927 3.445-1.158 5.1A.956.956 0 0 0 4.519 17.5L9 14.822l4.48 2.678a.957.957 0 0 0 1.424-1.034l-1.159-5.1 3.928-3.444a.957.957 0 0 0 .28-1.017Z"/>
                </svg>
                <h5 class="pt-2 md:pt-4 text-sm md:text-md font-bold">Работаем для клиента</h5>
                <div class="text-xs md:text-sm">100% положительных отзывов</div>
            </div>
            <div
                class="flex flex-col items-center gap-2 py-6 md:py-8 px-4 md:px-6 rounded-xl bg-purple text-white text-center">
                <svg class="w-10 md:w-12 h-10 md:h-12 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                     viewBox="0 0 18 18">
                    <path fill-rule="evenodd"
                          d="M0 2.572C0 1.507.863.643 1.929.643H16.07c1.067 0 1.93.864 1.93 1.929v10.285a1.928 1.928 0 0 1-1.929 1.929h-6.89l-4.023 2.476a.643.643 0 0 1-.98-.547v-1.93h-2.25A1.929 1.929 0 0 1 0 12.858V2.572Zm5.143 3.857a1.286 1.286 0 1 0 0 2.571 1.286 1.286 0 0 0 0-2.571Zm2.571 1.285a1.286 1.286 0 1 1 2.572 0 1.286 1.286 0 0 1-2.572 0Zm5.143-1.285a1.286 1.286 0 1 0 0 2.571 1.286 1.286 0 0 0 0-2.571Z"
                          clip-rule="evenodd"/>
                </svg>
                <h5 class="pt-2 md:pt-4 text-sm md:text-md font-bold">Поддержка</h5>
                <div class="text-xs md:text-sm">онлайн чат 24/7</div>
            </div>
        </div>
    </section>

    <section class="mt-16 lg:mt-24">
        <!-- Section heading -->
        <h2 class="text-lg lg:text-[42px] font-black">Категории</h2>

        <!-- Categories -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 md:gap-5 mt-8">
            @foreach($categories as $category)
                <a href="catalog.html"
                   class="p-3 sm:p-4 2xl:p-6 rounded-xl bg-card hover:bg-pink text-xxs sm:text-xs lg:text-sm text-white font-semibold">
                    {{ $category->title }}
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-16 lg:mt-24">
        <!-- Section heading -->
        <h2 class="text-lg lg:text-[42px] font-black">Каталог товаров</h2>

        <!-- Products list -->
        <div
            class="products grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-x-8 gap-y-8 lg:gap-y-10 2xl:gap-y-12 mt-8">
            @foreach($products as $product)
                <!-- Product card -->
                <div class="product-card flex flex-col rounded-3xl bg-card">
                    <a href="product.html" class="product-card-photo overflow-hidden h-[320px] rounded-3xl">
                        <img src="{{ asset($product->image) }}" class="object-cover w-full h-full"
                             alt="SteelSeries Aerox 3 Snow">
                    </a>
                    <div class="grow flex flex-col py-8 px-6">
                        <h3 class="text-sm lg:text-md font-black"><a href="product.html"
                                                                     class="inline-block text-white hover:text-pink">{{ $product->title }}</a>
                        </h3>
                        <div class="mt-auto pt-6">
                            <div class="mb-3 text-sm font-semibold">{{ $product->price }} ₽</div>
                            <div class="flex flex-wrap items-center gap-4">
                                <a href="#" class="w-[56px] !h-[56px] !px-0 btn btn-pink">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                                         viewBox="0 0 52 52">
                                        <path
                                            d="M39.385 38.663a6.047 6.047 0 1 0 6.041 6.053v-.006a6.053 6.053 0 0 0-6.041-6.047ZM50.11 9.706a2.329 2.329 0 0 0-.439-.042H12.852l-.583-3.902a5.248 5.248 0 0 0-5.196-4.519h-4.74a2.332 2.332 0 1 0 0 4.665h4.746a.583.583 0 0 1 .583.513l3.592 24.62a6.45 6.45 0 0 0 6.35 5.447H41.87a6.414 6.414 0 0 0 6.292-5.126l3.796-18.923a2.333 2.333 0 0 0-1.847-2.733ZM24.571 44.45a6.047 6.047 0 0 0-6.062-5.782 6.047 6.047 0 0 0 .14 12.089h.146a6.047 6.047 0 0 0 5.776-6.306Z"/>
                                    </svg>
                                </a>
                                <a href="#" class="w-[56px] !h-[56px] !px-0 btn btn-purple"
                                   title="Удалить из избранного">
                                    <!-- <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 52 52">
                                        <path d="M26 48.486c-.263 0-.526-.067-.762-.203-.255-.148-6.336-3.679-12.504-8.998-3.656-3.153-6.574-6.28-8.673-9.295C1.344 26.09-.022 22.338 0 18.84c.025-4.072 1.483-7.901 4.106-10.782 2.667-2.93 6.226-4.544 10.021-4.544 4.865 0 9.312 2.725 11.872 7.042 2.56-4.317 7.007-7.042 11.872-7.042 3.586 0 7.007 1.456 9.634 4.1 2.883 2.9 4.52 7 4.494 11.245-.022 3.493-1.414 7.24-4.137 11.134-2.105 3.013-5.02 6.14-8.66 9.291-6.146 5.32-12.183 8.85-12.437 8.997a1.524 1.524 0 0 1-.766.206ZM14.128 6.56c-2.927 0-5.686 1.26-7.768 3.548-2.115 2.324-3.292 5.431-3.313 8.75-.042 6.606 6.308 13.483 11.642 18.09 4.712 4.068 9.49 7.123 11.308 8.236 1.808-1.115 6.554-4.168 11.246-8.235 5.319-4.61 11.668-11.493 11.71-18.11.022-3.44-1.294-6.749-3.608-9.079-2.05-2.063-4.705-3.2-7.473-3.2-4.658 0-8.847 3.276-10.422 8.152a1.523 1.523 0 0 1-2.9 0C22.976 9.836 18.787 6.56 14.129 6.56Z"/>
                                    </svg> -->
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                         viewBox="0 0 52 52">
                                        <path
                                            d="M26 48.486c-.263 0-.526-.067-.762-.203-.255-.148-6.336-3.679-12.504-8.998-3.656-3.153-6.574-6.28-8.673-9.295C1.344 26.09-.022 22.338 0 18.84c.025-4.072 1.483-7.901 4.106-10.782 2.667-2.93 6.226-4.544 10.021-4.544 4.865 0 9.312 2.725 11.872 7.042 2.56-4.317 7.007-7.042 11.872-7.042 3.586 0 7.007 1.456 9.634 4.1 2.883 2.9 4.52 7 4.494 11.245-.022 3.493-1.414 7.24-4.137 11.135-2.105 3.012-5.02 6.138-8.66 9.29-6.146 5.32-12.183 8.85-12.437 8.997a1.524 1.524 0 0 1-.766.206Z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.product-card -->
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="catalog.html" class="btn btn-purple">Все товары &nbsp;→</a>
        </div>
    </section>

    <section class="mt-20">
        <!-- Section heading -->
        <h2 class="text-lg lg:text-[42px] font-black">Бренды</h2>

        <!-- Brands list -->
        <div class="grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-6 gap-4 md:gap-8 mt-12">
            @foreach($brands as $brand)
                <a href="catalog.html" class="p-6 rounded-xl bg-card hover:bg-card/60">
                    <div class="h-12 md:h-16">
                        <img src="{{ asset($brand->image) }}" class="object-contain w-full h-full"
                             alt="{{ $brand->title }}">
                    </div>
                    <div class="mt-8 text-xs sm:text-sm lg:text-md font-semibold text-center">{{ $brand->title }}</div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
