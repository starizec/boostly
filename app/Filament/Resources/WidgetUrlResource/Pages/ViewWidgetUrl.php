<?php

namespace App\Filament\Resources\WidgetUrlResource\Pages;

use App\Filament\Resources\WidgetUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWidgetUrl extends ViewRecord
{
    protected static string $resource = WidgetUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
} 