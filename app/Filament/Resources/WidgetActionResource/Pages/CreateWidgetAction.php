<?php

namespace App\Filament\Resources\WidgetActionResource\Pages;

use App\Filament\Resources\WidgetActionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWidgetAction extends CreateRecord
{
    protected static string $resource = WidgetActionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
