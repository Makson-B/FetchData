<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'company_id'
    ];


    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function tokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }
}
