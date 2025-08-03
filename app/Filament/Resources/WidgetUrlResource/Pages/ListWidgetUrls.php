<?php

namespace App\Filament\Resources\WidgetUrlResource\Pages;

use App\Filament\Resources\WidgetUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWidgetUrls extends ListRecords
{
    protected static string $resource = WidgetUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
