<?php

namespace App\Classes\Auth;

use Ramsey\Uuid\Uuid;

trait UuidForKey
{
    /**
     * Boot the Uuid trait for the model.
     *
     * @return void
     */
    public static function bootUuidForKey()
    {
        static::creating(function ($model) {
            $model->incrementing = false;
            $model->keyType = 'string';
            $model->{$model->getKeyName()} = (string)Uuid::uuid4();
        });
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts;
    }
}