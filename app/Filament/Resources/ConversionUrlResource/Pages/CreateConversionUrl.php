<?php

namespace App\Filament\Resources\ConversionUrlResource\Pages;

use App\Filament\Resources\ConversionUrlResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateConversionUrl extends CreateRecord
{
    protected static string $resource = ConversionUrlResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
