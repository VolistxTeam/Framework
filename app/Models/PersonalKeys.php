<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalKeys extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_keys';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $hidden = ['id'];

    protected $casts = [
        'created_at'  => 'date:Y-m-d H:i:s',
        'updated_at'  => 'date:Y-m-d H:i:s',
    ];
}
