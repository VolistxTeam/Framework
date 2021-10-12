<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalKey extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
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
        'id' => 'integer',
        'user_id' => 'integer',
        'max_count' => 'integer',
        'permissions' => 'array',
        'whitelist_range' => 'array',
        'activated_at' => 'date:Y-m-d H:i:s',
        'expires_at' => 'date:Y-m-d H:i:s',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}
