<?php

namespace App\Support\Traits\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;

trait WithSorting
{
    public function scopeSorted(Builder $query)
    {
        sorter()->run($query);
    }
}