<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'contact_id',
        'widget_id',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the user that owns the chat.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the widget associated with the chat.
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get the messages for the chat.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get the tags associated with the chat.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ChatTag::class, 'chat_tag')
            ->withTimestamps();
    }

    /**
     * Get the metadata associated with the chat.
     */
    public function meta(): HasOne
    {
        return $this->hasOne(ChatMeta::class);
    }

    /**
     * Scope a query to only include active chats.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include archived chats.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Get the latest message in the chat.
     */
    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }
} 