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
use Illuminate\Support\Facades\Auth;

class WidgetResource extends Resource
{
    protected static ?string $model = Widget::class;
    protected static ?string $navigationGroup = 'Postavke widgeta';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'Widgeti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('active')
                    ->label('Aktivan')
                    ->required()
                    ->default(true),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Naziv')
                            ->required()
                            ->maxLength(255),

                    ]),
                Forms\Components\Section::make('Prikaži na')
                    ->schema([
                        Forms\Components\Grid::make(7)
                            ->schema([
                                Forms\Components\Toggle::make('show_monday')
                                    ->label('Ponedjeljak')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('show_tuesday')
                                    ->label('Utorak')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('show_wednesday')
                                    ->label('Srijeda')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('show_thursday')
                                    ->label('Četvrtak')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('show_friday')
                                    ->label('Petak')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('show_saturday')
                                    ->label('Subota')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('show_sunday')
                                    ->label('Nedjelja')
                                    ->required()
                                    ->default(true),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('show_time_start')
                                    ->label('Početno vrijeme')
                                    ->required()
                                    ->default('00:00:00'),
                                Forms\Components\TimePicker::make('show_time_end')
                                    ->label('Završno vrijeme')
                                    ->required()
                                    ->default('23:59:59'),
                            ]),
                    ]),
                Forms\Components\Section::make('Offline forma')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Toggle::make('form_active')
                                    ->label('Forma aktivna')
                                    ->required()
                                    ->default(true),
                            ]),
                        Forms\Components\TextInput::make('send_to_email')
                            ->label('Pošalji na email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('offline_message')
                            ->label('Offline poruka')
                            ->maxLength(65535),

                    ]),
                Forms\Components\Section::make('Postavke')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('form_show_name')
                                    ->label('Prikaži ime')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('form_show_email')
                                    ->label('Prikaži email')
                                    ->required()
                                    ->default(true),
                                Forms\Components\Toggle::make('form_show_message')
                                    ->label('Prikaži poruku')
                                    ->required()
                                    ->default(true),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('button_text')
                                    ->label('Tekst gumba')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('start_button_text')
                                    ->label('Tekst početnog gumba')
                                    ->maxLength(255),
                            ]),

                    ]),
                Forms\Components\Section::make('Akcija')
                    ->schema([
                        Forms\Components\Select::make('action_id')
                            ->label('Akcija')
                            ->relationship('widgetAction', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Naziv')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->required()
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('button_text')
                                    ->label('Tekst gumba')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['user_id'] = Auth::id();
                                $action = \App\Models\WidgetAction::create($data);
                                
                                // Return the ID - Filament should auto-select this
                                return $action->id;
                            }),
                    ]),
                Forms\Components\Section::make('Medija')
                    ->schema([
                        Forms\Components\Select::make('media_id')
                            ->label('Medija')
                            ->relationship('media', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Naziv')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('url')
                                    ->label('Video datoteka')
                                    ->required()
                                    ->acceptedFileTypes(['video/mp4'])
                                    ->maxSize(100 * 1024) // 100MB
                                    ->directory('videos')
                                    ->preserveFilenames()
                                    ->downloadable()
                                    ->previewable(false)
                                    ->storeFileNamesIn('original_filename')
                                    ->visibility('public')
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $state) {
                                        if (! $state) return;
                                        
                                        if (blank($get('name'))) {
                                            $set('name', pathinfo($state->getClientOriginalName(), PATHINFO_FILENAME));
                                        }
                                    }),
                                Forms\Components\Hidden::make('mime_type')
                                    ->default('video/mp4'),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['user_id'] = Auth::id();
                                $media = \App\Models\Media::create($data);
                                return $media->id;
                            }),
                    ]),
                Forms\Components\Section::make('Stilovi')
                    ->schema([
                        Forms\Components\Select::make('style_id')
                            ->label('Stil')
                            ->relationship('style', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Stil #{$record->id}")
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('start_button_border_radius')
                                            ->label('Početni gumb - Border radius')
                                            ->numeric()
                                            ->default(5),
                                        Forms\Components\ColorPicker::make('start_button_background_color')
                                            ->label('Početni gumb - Boja pozadine')
                                            ->default('#007bff'),
                                        Forms\Components\ColorPicker::make('start_button_text_color')
                                            ->label('Početni gumb - Boja teksta')
                                            ->default('#ffffff'),
                                        Forms\Components\ColorPicker::make('start_button_hover_background_color')
                                            ->label('Početni gumb - Hover boja pozadine')
                                            ->default('#0056b3'),
                                        Forms\Components\ColorPicker::make('start_button_hover_text_color')
                                            ->label('Početni gumb - Hover boja teksta')
                                            ->default('#ffffff'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('chat_button_border_radius')
                                            ->label('Chat gumb - Border radius')
                                            ->numeric()
                                            ->default(5),
                                        Forms\Components\ColorPicker::make('chat_button_background_color')
                                            ->label('Chat gumb - Boja pozadine')
                                            ->default('#28a745'),
                                        Forms\Components\ColorPicker::make('chat_button_text_color')
                                            ->label('Chat gumb - Boja teksta')
                                            ->default('#ffffff'),
                                        Forms\Components\ColorPicker::make('chat_button_hover_background_color')
                                            ->label('Chat gumb - Hover boja pozadine')
                                            ->default('#1e7e34'),
                                        Forms\Components\ColorPicker::make('chat_button_hover_text_color')
                                            ->label('Chat gumb - Hover boja teksta')
                                            ->default('#ffffff'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('action_button_border_radius')
                                            ->label('Akcija gumb - Border radius')
                                            ->numeric()
                                            ->default(5),
                                        Forms\Components\ColorPicker::make('action_button_background_color')
                                            ->label('Akcija gumb - Boja pozadine')
                                            ->default('#ffc107'),
                                        Forms\Components\ColorPicker::make('action_button_text_color')
                                            ->label('Akcija gumb - Boja teksta')
                                            ->default('#212529'),
                                        Forms\Components\ColorPicker::make('action_button_hover_background_color')
                                            ->label('Akcija gumb - Hover boja pozadine')
                                            ->default('#e0a800'),
                                        Forms\Components\ColorPicker::make('action_button_hover_text_color')
                                            ->label('Akcija gumb - Hover boja teksta')
                                            ->default('#212529'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('widget_border_radius')
                                            ->label('Widget - Border radius')
                                            ->numeric()
                                            ->default(10),
                                        Forms\Components\ColorPicker::make('widget_background_color_1')
                                            ->label('Widget - Boja pozadine 1')
                                            ->default('#ffffff'),
                                        Forms\Components\ColorPicker::make('widget_background_color_2')
                                            ->label('Widget - Boja pozadine 2')
                                            ->default('#f8f9fa'),
                                        Forms\Components\TextInput::make('widget_background_url')
                                            ->label('Widget - URL pozadine')
                                            ->url(),
                                        Forms\Components\ColorPicker::make('widget_text_color')
                                            ->label('Widget - Boja teksta')
                                            ->default('#212529'),
                                        Forms\Components\TextInput::make('widget_width')
                                            ->label('Widget - Širina (px)')
                                            ->numeric()
                                            ->default(350),
                                        Forms\Components\TextInput::make('widget_height')
                                            ->label('Widget - Visina (px)')
                                            ->numeric()
                                            ->default(500),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\ColorPicker::make('widget_agent_buble_background_color')
                                            ->label('Agent balon - Boja pozadine')
                                            ->default('#e9ecef'),
                                        Forms\Components\ColorPicker::make('widget_agent_buble_color')
                                            ->label('Agent balon - Boja teksta')
                                            ->default('#212529'),
                                        Forms\Components\ColorPicker::make('widget_user_buble_background_color')
                                            ->label('Korisnik balon - Boja pozadine')
                                            ->default('#007bff'),
                                        Forms\Components\ColorPicker::make('widget_user_buble_color')
                                            ->label('Korisnik balon - Boja teksta')
                                            ->default('#ffffff'),
                                    ]),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $style = \App\Models\WidgetStyle::create($data);
                                return $style->id;
                            }),
                    ]),
                Forms\Components\Section::make('URL-ovi')
                    ->schema([
                        Forms\Components\Repeater::make('widgetUrls')
                            ->label('URL-ovi')
                            ->relationship('widgetUrls')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->required()
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://example.com'),
                            ])
                            ->addActionLabel('Dodaj URL')
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['url'] ?? null),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Aktivan')
                    ->boolean(),
                Tables\Columns\IconColumn::make('form_active')
                    ->label('Forma aktivna')
                    ->boolean(),
                Tables\Columns\TextColumn::make('send_to_email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Korisnik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kreiran')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ažuriran')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('form_active')
                    ->label('Forma aktivna'),
                Tables\Filters\SelectFilter::make('user')
                    ->label('Korisnik')
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


}
