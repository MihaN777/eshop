<a href="{{ route('catalog') }}" class="p-6 rounded-xl bg-card hover:bg-card/60">
    <div class="h-12 md:h-16">
        <img src="{{ asset($brand->storageImage()) }}" class="object-contain w-full h-full"
             alt="{{ $brand->title }}">
    </div>
    <div class="mt-8 text-xs sm:text-sm lg:text-md font-semibold text-center">{{ $brand->title }}</div>
</a>
