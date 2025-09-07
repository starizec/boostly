<?php

namespace App\Filament\Resources\ConversionUrlResource\Pages;

use App\Filament\Resources\ConversionUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConversionUrl extends EditRecord
{
    protected static string $resource = ConversionUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
