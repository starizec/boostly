<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Filament\Resources\StatusResource\RelationManagers;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Upravljanje razgovorima';

    protected static ?string $navigationLabel = 'Statusi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacije o statusu')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Naziv')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('npr. Aktivan, Na čekanju, Zatvoren')
                            ->helperText('Unesite opisni naziv za ovaj status'),
                        
                        Forms\Components\ColorPicker::make('color')
                            ->label('Boja')
                            ->required()
                            ->default('#3B82F6')
                            ->helperText('Odaberite boju koja predstavlja ovaj status'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Opis')
                            ->maxLength(500)
                            ->rows(3)
                            ->nullable()
                            ->placeholder('Opišite što ovaj status predstavlja (opcijalno)')
                            ->helperText('Dajte jasan opis kada se koristi ovaj status'),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Korisnik')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->nullable()
                            ->placeholder('Odaberite korisnika (opcijalno)')
                            ->helperText('Dodijelite ovaj status određenom korisniku')
                            ->visible(fn () => auth()->user()->hasRole('admin'))
                            ->default(fn () => auth()->user()->hasRole('admin') ? null : auth()->id())
                            ->disabled(fn () => !auth()->user()->hasRole('admin')),
                        
                        Forms\Components\Select::make('company_id')
                            ->label('Tvrtka')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->nullable()
                            ->placeholder('Odaberite tvrtku (opcijalno)')
                            ->helperText('Dodijelite ovaj status određenoj tvrtki')
                            ->visible(fn () => auth()->user()->hasRole('admin'))
                            ->default(fn () => auth()->user()->hasRole('admin') ? null : auth()->user()->company_id)
                            ->disabled(fn () => !auth()->user()->hasRole('admin')),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('color')
                    ->label('Boja')
                    ->formatStateUsing(function (string $state): string {
                        if (auth()->user()->hasRole('admin')) {
                            return view('components.color-preview', ['color' => $state]);
                        }
                        return view('components.color-circle-only', ['color' => $state]);
                    })
                    ->html(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Korisnik')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Nema dodijeljenog korisnika'),
                
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Tvrtka')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Nema dodijeljene tvrtke')
                    ->visible(fn () => auth()->user()->hasRole('admin')),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn () => auth()->user()->hasRole('admin')),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ažurirano')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn () => auth()->user()->hasRole('admin')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('color')
                    ->label('Boja')
                    ->options([
                        '#3B82F6' => 'Plava',
                        '#10B981' => 'Zelena',
                        '#F59E0B' => 'Žuta',
                        '#EF4444' => 'Crvena',
                        '#8B5CF6' => 'Ljubičasta',
                        '#F97316' => 'Narančasta',
                        '#06B6D4' => 'Cijan',
                        '#84CC16' => 'Lime',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Uredi'),
                Tables\Actions\DeleteAction::make()
                    ->label('Obriši'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Obriši odabrano'),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()->hasRole('admin')) {
            $data['user_id'] = auth()->id();
            $data['company_id'] = auth()->user()->company_id;
        }
        
        return $data;
    }

    public static function mutateFormDataBeforeUpdate(array $data): array
    {
        if (!auth()->user()->hasRole('admin')) {
            $data['user_id'] = auth()->id();
            $data['company_id'] = auth()->user()->company_id;
        }
        
        return $data;
    }
}
