<?php

namespace App\Filament\Resources\WidgetStyleResource\Pages;

use App\Filament\Resources\WidgetStyleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWidgetStyles extends ListRecords
{
    protected static string $resource = WidgetStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
