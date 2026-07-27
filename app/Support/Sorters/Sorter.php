<?php

namespace App\Support\Sorters;

use Illuminate\Contracts\Database\Eloquent\Builder;

final class Sorter
{
    public const SORT_KEY = 'sort';

    public const DIRECTION_ASC = 'ASC';

    public const DIRECTION_DESC = 'DESC';

    /**
     * @param array<int, string> $columns Whitelist колонок, по которым разрешена сортировка
     */
    public function __construct(
        protected array $columns = []
    )
    {
    }

    public function run(Builder $query): Builder
    {
        $column = $this->column();

        if (is_null($column)) {
            return $query;
        }

        return $query->orderBy($column, $this->direction());
    }

    public function key(): string
    {
        return self::SORT_KEY;
    }

    /**
     * @return array<int, string>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * Значение параметра сортировки из запроса.
     *
     * Нестроковый вход (?sort[]=price) отбрасывается.
     */
    public function sortData(): string
    {
        $sort = request()->input($this->key());

        return is_string($sort) ? $sort : '';
    }

    /**
     * Колонка сортировки из запроса, если она есть в whitelist; иначе null.
     */
    public function column(): ?string
    {
        $sort = $this->sortData();
        $column = str_starts_with($sort, '-') ? substr($sort, 1) : $sort;

        return in_array($column, $this->columns(), true) ? $column : null;
    }

    public function direction(): string
    {
        return str_starts_with($this->sortData(), '-')
            ? self::DIRECTION_DESC
            : self::DIRECTION_ASC;
    }

    /**
     * Нормализованное значение сортировки для ссылок и полей формы: 'price', '-price' или ''.
     */
    public function current(): string
    {
        $column = $this->column();

        if (is_null($column)) {
            return '';
        }

        return $this->direction() === self::DIRECTION_DESC ? '-' . $column : $column;
    }

    public function isActive(string $column, string $direction = self::DIRECTION_ASC): bool
    {
        return $this->column() === trim($column, '-')
            && strtolower($this->direction()) === strtolower($direction);
    }
}
