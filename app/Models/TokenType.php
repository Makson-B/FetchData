<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TokenType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type'
    ];

    public function tokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }
}
