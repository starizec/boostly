<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WidgetResource\Pages;
use App\Models\Widget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WidgetResource extends Resource
{
    protected static ?string $model = Widget::class;
    protected static ?string $navigationGroup = 'Postavke widgeta';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('form_active')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('form_show_name')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('form_show_email')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('form_show_message')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('show_monday')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('show_tuesday')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('show_wednesday')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('show_thursday')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('show_friday')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('show_saturday')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('show_sunday')
                    ->required()
                    ->default(true),
                Forms\Components\TimePicker::make('show_time_start')
                    ->required()
                    ->default('00:00:00'),
                Forms\Components\TimePicker::make('show_time_end')
                    ->required()
                    ->default('23:59:59'),
                Forms\Components\Textarea::make('offline_message')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('send_to_email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('button_text')
                    ->maxLength(255),
                Forms\Components\TextInput::make('start_button_text')
                    ->maxLength(255),
                Forms\Components\Select::make('action_id')
                    ->relationship('widgetAction', 'name')
                    ->required(),
                Forms\Components\Select::make('media_id')
                    ->relationship('media', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Textarea::make('urls')
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('form_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('send_to_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('form_active'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListWidgets::route('/'),
            'create' => Pages\CreateWidget::route('/create'),
            'edit' => Pages\EditWidget::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('id');
    }
}
