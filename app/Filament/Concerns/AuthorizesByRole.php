<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Filament\Authorization\ResourceAccess;

trait AuthorizesByRole
{
    public static function canAccess(): bool
    {
        return ResourceAccess::forResource(static::class);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
