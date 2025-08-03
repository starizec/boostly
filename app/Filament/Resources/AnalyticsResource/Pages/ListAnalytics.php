<?php

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnalytics extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
