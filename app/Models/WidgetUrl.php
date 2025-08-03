<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'widget_id'
    ];

    protected $casts = [
        'url' => 'string'
    ];

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get the domain from the URL
     */
    public function getDomainAttribute(): string
    {
        return parse_url($this->url, PHP_URL_HOST) ?? '';
    }

    /**
     * Get the path from the URL
     */
    public function getPathAttribute(): string
    {
        return parse_url($this->url, PHP_URL_PATH) ?? '';
    }

    /**
     * Scope to get URLs for a specific widget
     */
    public function scopeForWidget($query, $widgetId)
    {
        return $query->where('widget_id', $widgetId);
    }

    /**
     * Scope to get widgets for a specific URL
     */
    public function scopeForUrl($query, $url)
    {
        return $query->where('url', $url);
    }
}
