<?php

namespace App\Filament\Resources\ConversionUrlResource\Pages;

use App\Filament\Resources\ConversionUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConversionUrls extends ListRecords
{
    protected static string $resource = ConversionUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
