<?php

declare(strict_types=1);

namespace App\Filament\Authorization;

use App\Filament\Resources\AnalyticsResource;
use App\Filament\Resources\ChatResource;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\ConversionUrlResource;
use App\Filament\Resources\MediaResource;
use App\Filament\Resources\StatusResource;
use App\Filament\Resources\TagResource;
use App\Filament\Resources\WidgetActionResource;
use App\Filament\Resources\WidgetResource;
use App\Filament\Resources\WidgetStyleResource;
use App\Filament\Resources\WidgetUrlResource;
use App\Models\User;

class ResourceAccess
{
    /**
     * Filament resources visible to users with the "user" role.
     */
    private const USER_ALLOWED_RESOURCES = [
        AnalyticsResource::class,
        WidgetResource::class,
        WidgetActionResource::class,
        WidgetStyleResource::class,
        StatusResource::class,
        TagResource::class,
        WidgetUrlResource::class,
        ConversionUrlResource::class,
        ContactResource::class,
        MediaResource::class,
        ChatResource::class,
    ];

    public static function forResource(string $resourceClass): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasRole('user')) {
            return in_array($resourceClass, self::USER_ALLOWED_RESOURCES, true);
        }

        return false;
    }

    public static function canAccessChatList(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    public static function canAccessChatInterface(): bool
    {
        return self::forResource(ChatResource::class);
    }

    public static function canAccessDashboard(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }
}
