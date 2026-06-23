<?php

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnalytics extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;

    protected static ?string $navigationLabel = 'Događaji';

    protected static ?string $title = 'Analitički događaji';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('dashboard')
                ->label('Pregled')
                ->icon('heroicon-o-chart-bar')
                ->url(AnalyticsResource::getUrl('index')),
            Actions\CreateAction::make()
                ->visible(fn (): bool => AnalyticsResource::canCreate()),
        ];
    }
}
