<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Status extends Model
{
    protected $fillable = [
        'name',
        'color',
        'description',
        'user_id',
        'company_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and add global scopes.
     */
    protected static function boot()
    {
        parent::boot();

        // Global scope to filter statuses based on user role and company
        static::addGlobalScope('user_company_scope', function (Builder $builder) {
            if (auth()->check()) {
                if (!auth()->user()->hasRole('admin')) {
                    // Non-admin users only see their own statuses from their company
                    $builder->where('user_id', auth()->id())
                           ->where('company_id', auth()->user()->company_id);
                }
                // Admin users can see ALL statuses from ALL companies (no filtering)
            }
        });
    }

    /**
     * Get the user that owns the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the status.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
