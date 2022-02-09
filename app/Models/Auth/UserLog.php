<?php

namespace App\Models\Auth;

use App\Classes\Auth\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLog extends Model
{
    use HasFactory;
    use UuidForKey;

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


    protected $fillable = [
        'subscription_id',
        'url',
        'method',
        'ip',
        'user_agent',
    ];

    protected $casts = [
    ];
}
