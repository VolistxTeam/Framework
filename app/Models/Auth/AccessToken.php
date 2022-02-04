<?php

namespace App\Models\Auth;

use App\Classes\Auth\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessToken extends Model
{
    use HasFactory;
    use UuidForKey;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'secret',
        'secret_salt',
        'permissions',
        'whitelist_range'
    ];

    protected $casts = [
        'permissions' => 'array',
        'whitelist_range' => 'array'
    ];
}
