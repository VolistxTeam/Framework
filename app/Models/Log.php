<?php

namespace App\Models;

use App\Classes\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
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

    protected $casts = [
        'request_info' => 'array',
        'created_at' => 'date:Y-m-d H:i:s'
    ];

    protected $fillable = [
        'personal_token_id',
        'key',
        'value',
        'type'
    ];

    protected $hidden = ['id', 'personal_token_id'];
}
