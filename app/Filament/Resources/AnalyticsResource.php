<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsResource\Pages;
use App\Filament\Resources\AnalyticsResource\RelationManagers;
use App\Models\Analytics;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnalyticsResource extends Resource
{
    protected static ?string $model = Analytics::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $modelLabel = 'Analytics Event';

    protected static ?string $pluralModelLabel = 'Analytics Events';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('widget_id')
                    ->label('Widget')
                    ->relationship('widget', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Select::make('event')
                    ->label('Event Type')
                    ->options([
                        Analytics::EVENT_LOADED => 'Widget Loaded',
                        Analytics::EVENT_OPENED => 'Widget Opened',
                        Analytics::EVENT_ACTION_CLICKED => 'Action Clicked',
                        Analytics::EVENT_CHAT_CLICKED => 'Chat Clicked',
                        Analytics::EVENT_CHAT_STARTED => 'Chat Started',
                        Analytics::EVENT_CONVERSION => 'Conversion',
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->maxLength(2048)
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('data')
                    ->label('Event Data')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('widget.name')
                    ->label('Widget')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('event')
                    ->label('Event Type')
                    ->colors([
                        'primary' => Analytics::EVENT_LOADED,
                        'success' => Analytics::EVENT_OPENED,
                        'warning' => Analytics::EVENT_ACTION_CLICKED,
                        'info' => Analytics::EVENT_CHAT_CLICKED,
                        'secondary' => Analytics::EVENT_CHAT_STARTED,
                        'danger' => Analytics::EVENT_CONVERSION,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Analytics::EVENT_LOADED => 'Widget Loaded',
                        Analytics::EVENT_OPENED => 'Widget Opened',
                        Analytics::EVENT_ACTION_CLICKED => 'Action Clicked',
                        Analytics::EVENT_CHAT_CLICKED => 'Chat Clicked',
                        Analytics::EVENT_CHAT_STARTED => 'Chat Started',
                        Analytics::EVENT_CONVERSION => 'Conversion',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('data')
                    ->label('Event Data')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return 'No data';
                        }
                        
                        // Ensure we're working with an array
                        $data = is_string($state) ? json_decode($state, true) : $state;
                        
                        if (!is_array($data) || empty($data)) {
                            return 'No data';
                        }
                        
                        return count($data) . ' field(s)';
                    })
                    ->tooltip(function ($record) {
                        if (empty($record->data)) {
                            return null;
                        }
                        
                        // Ensure we're working with an array for the tooltip
                        $data = is_string($record->data) ? json_decode($record->data, true) : $record->data;
                        
                        if (!is_array($data)) {
                            return $record->data;
                        }
                        
                        return json_encode($data, JSON_PRETTY_PRINT);
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('widget_id')
                    ->label('Widget')
                    ->relationship('widget', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('event')
                    ->label('Event Type')
                    ->options([
                        Analytics::EVENT_LOADED => 'Widget Loaded',
                        Analytics::EVENT_OPENED => 'Widget Opened',
                        Analytics::EVENT_ACTION_CLICKED => 'Action Clicked',
                        Analytics::EVENT_CHAT_CLICKED => 'Chat Clicked',
                        Analytics::EVENT_CHAT_STARTED => 'Chat Started',
                        Analytics::EVENT_CONVERSION => 'Conversion',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalytics::route('/'),
            'create' => Pages\CreateAnalytics::route('/create'),
            'view' => Pages\ViewAnalytics::route('/{record}'),
            'edit' => Pages\EditAnalytics::route('/{record}/edit'),
        ];
    }
}
