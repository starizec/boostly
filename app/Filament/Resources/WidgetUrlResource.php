<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WidgetUrlResource\Pages;
use App\Filament\Resources\WidgetUrlResource\RelationManagers;
use App\Models\WidgetUrl;
use App\Models\Widget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class WidgetUrlResource extends Resource
{
    protected static ?string $model = WidgetUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Widget Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Widget URL';

    protected static ?string $pluralModelLabel = 'Widget URLs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Widget URL Connection')
                    ->schema([
                        Select::make('widget_id')
                            ->label('Widget')
                            ->options(Widget::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the widget to display on this URL'),

                        TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->url()
                            ->placeholder('https://example.com')
                            ->helperText('Enter the full URL where the widget should be displayed')
                            ->maxLength(255),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('widget.name')
                    ->label('Widget')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(50)
                    ->copyable()
                    ->sortable(),

                TextColumn::make('domain')
                    ->label('Domain')
                    ->getStateUsing(fn (WidgetUrl $record): string => $record->domain)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('path')
                    ->label('Path')
                    ->getStateUsing(fn (WidgetUrl $record): string => $record->path)
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('widget_id')
                    ->label('Widget')
                    ->options(Widget::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
                    })
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
            'index' => Pages\ListWidgetUrls::route('/'),
            'create' => Pages\CreateWidgetUrl::route('/create'),
            'view' => Pages\ViewWidgetUrl::route('/{record}'),
            'edit' => Pages\EditWidgetUrl::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['widget']);
    }
}
