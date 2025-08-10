<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Filament\Resources\TagResource\RelationManagers;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Upravljanje razgovorima';

    protected static ?string $navigationLabel = 'Oznake';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacije o oznaci')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Naziv')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('npr. Hitno, Podrška, Prodaja')
                            ->helperText('Unesite opisni naziv za ovu oznaku')
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\ColorPicker::make('color')
                            ->label('Boja')
                            ->required()
                            ->default('#3B82F6')
                            ->helperText('Odaberite boju koja predstavlja ovu oznaku'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Opis')
                            ->maxLength(500)
                            ->rows(3)
                            ->nullable()
                            ->placeholder('Opišite što ova oznaka predstavlja (opcijalno)')
                            ->helperText('Dajte jasan opis kada se koristi ova oznaka'),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Korisnik')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->nullable()
                            ->placeholder('Odaberite korisnika (opcijalno)')
                            ->helperText('Dodijelite ovu oznaku određenom korisniku')
                            ->visible(fn () => auth()->user()->hasRole('admin'))
                            ->default(fn () => auth()->user()->hasRole('admin') ? null : auth()->id())
                            ->disabled(fn () => !auth()->user()->hasRole('admin')),
                        
                        Forms\Components\Select::make('company_id')
                            ->label('Tvrtka')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->nullable()
                            ->placeholder('Odaberite tvrtku (opcijalno)')
                            ->helperText('Dodijelite ovu oznaku određenoj tvrtki')
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
                
                Tables\Columns\TextColumn::make('chats_count')
                    ->label('Razgovori')
                    ->counts('chats')
                    ->badge()
                    ->color('warning'),
                
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
                
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Tvrtka')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole('admin')),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Korisnik')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole('admin')),

                Filter::make('has_chats')
                    ->label('Ima razgovore')
                    ->query(fn (Builder $query): Builder => $query->whereHas('chats')),

                Filter::make('no_chats')
                    ->label('Nema razgovora')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('chats')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Uredi'),
                Tables\Actions\DeleteAction::make()
                    ->label('Obriši')
                    ->before(function (Tag $record) {
                        if ($record->hasChats()) {
                            throw new \Exception('Ne možete obrisati oznaku koja je povezana s razgovorima. Molimo prvo uklonite oznaku iz svih razgovora.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Obriši odabrano')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->hasChats()) {
                                    throw new \Exception("Ne možete obrisati oznaku '{$record->name}' koja je povezana s razgovorima. Molimo prvo uklonite oznaku iz svih razgovora.");
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->searchable()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChatsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('chats')
            ->with(['company', 'user']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
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
