<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_id',
        'agent_id', 
        'message',
        'type',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];
}
