<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Authorization\ResourceAccess;

class Dashboard extends \Filament\Pages\Dashboard
{
    public static function canAccess(): bool
    {
        return ResourceAccess::canAccessDashboard();
    }
}
