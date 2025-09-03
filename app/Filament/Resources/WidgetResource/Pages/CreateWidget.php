<?php

namespace App\Filament\Resources\WidgetResource\Pages;

use App\Filament\Resources\WidgetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWidget extends CreateRecord
{
    protected static string $resource = WidgetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }


}
