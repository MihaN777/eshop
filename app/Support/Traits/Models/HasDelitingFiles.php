<?php

namespace App\Support\Traits\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

trait HasDelitingFiles
{
    protected static function bootHasDelitingFiles(): void
    {
        static::deleted(function (Model $model) {
            $model->deleteFileHandle();
        });
    }

    protected function deleteFileHandle(): void
    {
        foreach ($this->deleteFileColumns() as $column) {
            if (!isset($this->{$column})) continue;

            $filePath = (string)$this->{$column};
            $msgExists = "Не существует файл для удаления: {$filePath}";
            $msgDelete = "Не удалось удалить файл: {$filePath}";

            if (!Storage::disk($this->deleteFileStorageDisk())->exists($filePath))
            {
                logger()->info($msgExists);
                logger()->channel('telegram')->info($msgExists);
                continue;
            }

            // TODO удаление при beginTransaction (transactionLevel)
            if (!Storage::disk($this->deleteFileStorageDisk())->delete($filePath)) {
                logger()->info($msgDelete);
                logger()->channel('telegram')->info($msgDelete);
            }
        }
    }

    protected function deleteFileColumns(): array
    {
        return [];
    }

    protected function deleteFileStorageDisk(): ?string
    {
        return null;
    }
}
