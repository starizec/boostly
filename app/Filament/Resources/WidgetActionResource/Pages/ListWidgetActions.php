<?php

namespace App\Filament\Resources\WidgetActionResource\Pages;

use App\Filament\Resources\WidgetActionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWidgetActions extends ListRecords
{
    protected static string $resource = WidgetActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
