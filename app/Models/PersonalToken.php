<?php

namespace App\Models;

use App\Classes\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalToken extends Model
{
    use HasFactory;
    use UuidForKey;


    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'key',
        'secret',
        'secret_salt',
        'max_count',
        'permissions',
        'whitelist_range',
        'activated_at',
        'expires_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'max_count' => 'integer',
        'permissions' => 'array',
        'whitelist_range' => 'array',
        'activated_at' => 'date:Y-m-d H:i:s',
        'expires_at' => 'date:Y-m-d H:i:s',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }
}
