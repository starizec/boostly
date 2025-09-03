<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WidgetStyleResource\Pages;
use App\Filament\Resources\WidgetStyleResource\RelationManagers;
use App\Models\WidgetStyle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WidgetStyleResource extends Resource
{
    protected static ?string $model = WidgetStyle::class;
    protected static ?string $navigationGroup = 'Postavke widgeta';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('widget_id')
                    ->relationship('widget', 'name')
                    ->required(),

                Forms\Components\Section::make('Start Button Styles')
                    ->schema([
                        Forms\Components\TextInput::make('start_button_border_radius')
                            ->numeric()
                            ->default(10),
                        Forms\Components\ColorPicker::make('start_button_background_color')
                            ->default('#000000'),
                        Forms\Components\ColorPicker::make('start_button_text_color')
                            ->default('#FFFFFF'),
                        Forms\Components\ColorPicker::make('start_button_hover_background_color')
                            ->default('#333333'),
                        Forms\Components\ColorPicker::make('start_button_hover_text_color')
                            ->default('#FFFFFF'),
                    ])->columns(2),

                Forms\Components\Section::make('Chat Button Styles')
                    ->schema([
                        Forms\Components\TextInput::make('chat_button_border_radius')
                            ->numeric()
                            ->default(10),
                        Forms\Components\ColorPicker::make('chat_button_background_color')
                            ->default('#000000'),
                        Forms\Components\ColorPicker::make('chat_button_text_color')
                            ->default('#FFFFFF'),
                        Forms\Components\ColorPicker::make('chat_button_hover_background_color')
                            ->default('#333333'),
                        Forms\Components\ColorPicker::make('chat_button_hover_text_color')
                            ->default('#FFFFFF'),
                    ])->columns(2),

                Forms\Components\Section::make('Action Button Styles')
                    ->schema([
                        Forms\Components\TextInput::make('action_button_border_radius')
                            ->numeric()
                            ->default(10),
                        Forms\Components\ColorPicker::make('action_button_background_color')
                            ->default('#000000'),
                        Forms\Components\ColorPicker::make('action_button_text_color')
                            ->default('#FFFFFF'),
                        Forms\Components\ColorPicker::make('action_button_hover_background_color')
                            ->default('#333333'),
                        Forms\Components\ColorPicker::make('action_button_hover_text_color')
                            ->default('#FFFFFF'),
                    ])->columns(2),

                Forms\Components\Section::make('Widget Container Styles')
                    ->schema([
                        Forms\Components\TextInput::make('widget_border_radius')
                            ->numeric()
                            ->default(10),
                        Forms\Components\ColorPicker::make('widget_background_color_1')
                            ->default('#FFFFFF'),
                        Forms\Components\ColorPicker::make('widget_background_color_2')
                            ->nullable(),
                        Forms\Components\TextInput::make('widget_background_url')
                            ->url()
                            ->nullable(),
                        Forms\Components\ColorPicker::make('widget_text_color')
                            ->default('#000000'),
                        Forms\Components\TextInput::make('widget_width')
                            ->default('300px'),
                        Forms\Components\TextInput::make('widget_height')
                            ->default('500px'),
                    ])->columns(2),

                Forms\Components\Section::make('Chat Bubble Styles')
                    ->schema([
                        Forms\Components\ColorPicker::make('widget_agent_buble_background_color')
                            ->default('#F0F0F0'),
                        Forms\Components\ColorPicker::make('widget_agent_buble_color')
                            ->default('#000000'),
                        Forms\Components\ColorPicker::make('widget_user_buble_background_color')
                            ->default('#000000'),
                        Forms\Components\ColorPicker::make('widget_user_buble_color')
                            ->default('#FFFFFF'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('widget.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('widget_background_color_1')
                    ->label('Background Color'),
                Tables\Columns\ColorColumn::make('widget_text_color')
                    ->label('Text Color'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWidgetStyles::route('/'),
            'create' => Pages\CreateWidgetStyle::route('/create'),
            'edit' => Pages\EditWidgetStyle::route('/{record}/edit'),
        ];
    }
}
