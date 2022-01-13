<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

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
        'personal_key_id',
        'request_id',
        'request_info',
        'access_ip'
    ];

    protected $hidden = ['id', 'personal_key_id'];
}
