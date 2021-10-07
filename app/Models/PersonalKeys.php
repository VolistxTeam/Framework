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

    protected $fillable = [
        'user_id',
        'key',
        'max_count',
        'permissions',
        'whitelist_range',
        'activated_at',
        'expires_at'
    ];

    protected $casts = [
        'max_count' => 'integer',
        'permissions' => 'array',
        'whitelist_range' => 'array',
        'activated_at'  => 'date:Y-m-d H:i:s',
        'expires_at'  => 'date:Y-m-d H:i:s',
        'created_at'  => 'date:Y-m-d H:i:s',
        'updated_at'  => 'date:Y-m-d H:i:s',
    ];
}
