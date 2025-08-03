<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetAction extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'button_text'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
