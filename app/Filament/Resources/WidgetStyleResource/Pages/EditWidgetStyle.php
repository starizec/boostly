<?php

namespace App\Filament\Resources\WidgetStyleResource\Pages;

use App\Filament\Resources\WidgetStyleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWidgetStyle extends EditRecord
{
    protected static string $resource = WidgetStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
