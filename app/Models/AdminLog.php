<?php

namespace App\Models;

use App\Classes\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
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
        'access_token_id',
        'url',
        'request_method',
        'request_body',
        'request_header',
        'ip',
        'response_code',
        'response_body',
    ];

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(AccessToken::class);
    }
}
