<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'account_id',
        'api_service_id',
        'token_type_id'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function api_service(): BelongsTo
    {
        return $this->belongsTo(ApiService::class);
    }

    public function token_type(): BelongsTo
    {
        return $this->belongsTo(TokenType::class);
    }
}
