<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversionUrlResource\Pages;
use App\Models\ConversionUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversionUrlResource extends Resource
{
    protected static ?string $model = ConversionUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Conversion URLs';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conversion URL Information')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/thank-you')
                            ->helperText('Enter the URL that represents a conversion or goal completion'),
                    ])
                    ->columns(1),
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
                
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->copyable()
                    ->copyMessage('URL copied')
                    ->copyMessageDuration(1500),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListConversionUrls::route('/'),
            'create' => Pages\CreateConversionUrl::route('/create'),
            'edit' => Pages\EditConversionUrl::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
