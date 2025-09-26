<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Model;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function (Model $model) {
            $model->makeSlug();
        });
    }

    protected function makeSlug(): void
    {
        if (!$this->{$this->slugColumn()}) {
            $slug = $this->slugUnique(
                str($this->{$this->slugFrom()})
                    ->slug()
                    ->value()
            );

            $this->{$this->slugColumn()} = $slug;
        }
    }

    protected function slugColumn(): string
    {
        return 'slug';
    }

    protected function slugFrom(): string
    {
        return 'title';
    }

    private function slugUnique(string $slug): string
    {
        $originalSlug = $slug;
        $i = 0;

        while ($this->isSlugExists($slug)) {
            $i++;
            $slug = $originalSlug . '-' . $i;
        }

        return $slug;
    }

    private function isSlugExists(string $slug): bool
    {
        $query = $this->newQuery()
            ->where(self::slugColumn(), $slug)
            // ->where($this->getKeyName(), '!=', $this->getKey()) // Не текущая запись: только при updating
            // SoftDeletes
            ->withoutGlobalScopes(); // Исключить глобальные диапазоны запроса (если такие есть) и проверять по всей таблице

        return $query->exists();
    }
}
