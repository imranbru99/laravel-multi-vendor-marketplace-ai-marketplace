<?php

namespace App\Models\Concerns;

use App\Models\SlugRedirect;
use Illuminate\Validation\ValidationException;

trait HasSlugHistory
{
    public static function bootHasSlugHistory(): void
    {
        static::saving(function ($model) {
            if (! $model->isDirty('slug')) {
                return;
            }
            $reserved = SlugRedirect::where('entity', class_basename($model))->where('slug', $model->slug)->first();
            if ($reserved && $reserved->record_id !== $model->id) {
                throw ValidationException::withMessages(['slug' => 'This URL belongs to another historical record.']);
            }
        });
        static::updated(function ($model) {
            if ($model->wasChanged('slug') && $model->getRawOriginal('slug')) {
                SlugRedirect::firstOrCreate(['entity' => class_basename($model), 'slug' => $model->getRawOriginal('slug')], ['record_id' => $model->id]);
            }
        });
    }
}
