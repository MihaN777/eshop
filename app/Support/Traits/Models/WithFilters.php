<?php

namespace App\Support\Traits\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

trait WithFilters
{
    public function scopeFiltered(Builder $query)
    {
        // Пропустить $query через pipeline и вернуть сформированый с учетом фильтров запрос
        return app(Pipeline::class)
            ->send($query)
            ->through(filters())
            // ->via('handle') // Вызываемый метод pipeline'ом в фильтрах (иначе __invoke())
            ->thenReturn();

        // foreach (filters() as $filter) {
        //     $query = $filter->apply($query);
        // }
    }
}