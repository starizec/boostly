<?php

namespace App\Filament\Resources\WidgetStyleResource\Pages;

use App\Filament\Resources\WidgetStyleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWidgetStyle extends CreateRecord
{
    protected static string $resource = WidgetStyleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
