<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetUrl extends Model
{
    protected $fillable = [
        'url',
        'widget_id'
    ];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
