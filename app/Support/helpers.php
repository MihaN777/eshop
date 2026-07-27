<?php

use App\Models\Category;
use App\Support\Cart\CartManager;
use App\Support\Filters\FilterManager;
use App\Support\Session\Flash\Flash;
use App\Support\Sorters\Sorter;

if (!function_exists('flash')) {
    function flash(): Flash
    {
        return app(Flash::class);
    }
}

if (!function_exists('sorter')) {
    function sorter(): Sorter
    {
        return app(Sorter::class);
    }
}

if (!function_exists('filters')) {
    function filters(): array
    {
        return app(FilterManager::class)->items();
    }
}

if (!function_exists('cart')) {
    function cart(): CartManager
    {
        return app(CartManager::class);
    }
}

if (!function_exists('is_catalog_view')) {
    function is_catalog_view(string $type, string $default = 'grid'): bool
    {
        return session('view', $default) === $type;
    }
}

if (!function_exists('filter_url')) {
    function filter_url(?Category $category, array $params = []): string
    {
        $query = [
            ...request()->only(['filters']),
            'sort' => sorter()->current(),
            ...$params,
            'category' => $category
        ];

        // Пустая сортировка не должна висеть в ссылке; сюда же попадает
        // значение, отброшенное whitelist'ом сортировщика.
        if (($query['sort'] ?? '') === '') {
            unset($query['sort']);
        }

        return route('catalog', $query);
    }
}
