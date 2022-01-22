<?php

namespace App\Models;

use App\Classes\UuidForKey;
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
        'personal_token_id',
        'url',
        'request_method',
        'request_body',
        'request_header',
        'ip',
        'response_code',
        'response_body',
    ];

    protected $casts = [
        'header'=>'array'
    ];

    public function personalToken(): BelongsTo
    {
        return $this->belongsTo(PersonalToken::class);
    }
}
