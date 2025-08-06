<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMeta extends Model
{
    use HasFactory;

    protected $table = 'chat_meta';

    protected $fillable = [
        'chat_id',
        'ip_address',
        'country',
        'city',
        'region',
        'latitude',
        'longitude',
        'user_agent',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the chat that owns the metadata.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
} 