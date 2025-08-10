<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'color',
        'description',
        'company_id',
        'user_id',
        'default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
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

        // Global scope to filter tags based on user role and company
        static::addGlobalScope('user_company_scope', function (Builder $builder) {
            if (auth()->check()) {
                if (!auth()->user()->hasRole('admin')) {
                    // Non-admin users only see their own tags from their company
                    $builder->where('user_id', auth()->id())
                           ->where('company_id', auth()->user()->company_id);
                }
                // Admin users can see ALL tags from ALL companies (no filtering)
            }
        });
    }

    /**
     * Get the company that owns the tag.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that created the tag.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chats that are tagged with this tag.
     */
    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_tag')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include tags for a specific company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include tags created by a specific user.
     */
    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the tag's display name with color.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Check if the tag is associated with any chats.
     */
    public function hasChats(): bool
    {
        return $this->chats()->exists();
    }

    /**
     * Get the count of chats using this tag.
     */
    public function getChatsCountAttribute(): int
    {
        return $this->chats()->count();
    }
}
