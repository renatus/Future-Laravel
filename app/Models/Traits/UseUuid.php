<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

/**
 * Make model use UUIDs instead of IDs
 */
trait UseUuid
{
    protected static function bootUseUuid()
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::orderedUuid();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
