<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use App\Filament\Widgets\AnalyticsStatsOverview;
use App\Models\Analytics;
use App\Models\User;
use App\Models\Widget;
use App\Services\AnalyticsDashboardService;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AnalyticsDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = AnalyticsResource::class;

    protected static string $view = 'filament.resources.analytics-resource.pages.analytics-dashboard';

    protected static ?string $title = 'Analitika';

    protected static ?string $navigationLabel = 'Pregled';

    /** @var array<int, array<string, int>> */
    public array $analyticsCounts = [];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $data = app(AnalyticsDashboardService::class)->getDashboardData($user);
        $this->analyticsCounts = $data['analyticsCounts'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('events')
                ->label('Svi događaji')
                ->icon('heroicon-o-list-bullet')
                ->url(AnalyticsResource::getUrl('events')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticsStatsOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        $eventColumns = collect(Analytics::getEventTypes())
            ->map(function (string $eventType): TextColumn {
                return TextColumn::make($eventType)
                    ->label(ucwords(str_replace('_', ' ', $eventType)))
                    ->badge()
                    ->color('primary')
                    ->getStateUsing(fn (Widget $record): int => $this->analyticsCounts[$record->id][$eventType] ?? 0);
            })
            ->all();

        return $table
            ->query($this->getWidgetsQuery())
            ->heading('Widgeti')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),
                ...$eventColumns,
                TextColumn::make('active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktivan' : 'Neaktivan')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('user.name')
                    ->label('Korisnik')
                    ->placeholder('N/A')
                    ->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
            ])
            ->defaultSort('id')
            ->paginated([10, 25, 50]);
    }

    protected function getWidgetsQuery(): Builder
    {
        $query = Widget::query();

        $user = Auth::user();

        if ($user instanceof User && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('id');
    }
}
