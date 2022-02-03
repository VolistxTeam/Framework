<?php

namespace App\Models\Auth;

use App\Classes\Auth\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plans';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function subscriptions(): HasMany
    {
        return $this->HasMany(Subscription::class);
    }
}