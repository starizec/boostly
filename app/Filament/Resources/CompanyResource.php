<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Business Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Company Name'),
                        
                        Forms\Components\TextInput::make('vat_number')
                            ->maxLength(255)
                            ->label('VAT Number')
                            ->placeholder('Enter VAT/Tax ID'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Address Information')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255)
                            ->label('Street Address'),
                        
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255)
                            ->label('City'),
                        
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255)
                            ->label('State/Province'),
                        
                        Forms\Components\TextInput::make('zip')
                            ->maxLength(20)
                            ->label('ZIP/Postal Code'),
                        
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Country')
                            ->placeholder('Select a country'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Company Name'),
                
                Tables\Columns\TextColumn::make('vat_number')
                    ->searchable()
                    ->sortable()
                    ->label('VAT Number'),
                
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->sortable()
                    ->label('Address')
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->label('City'),
                
                Tables\Columns\TextColumn::make('state')
                    ->searchable()
                    ->sortable()
                    ->label('State'),
                
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable()
                    ->label('Country'),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created At'),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Updated At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->label('Filter by Country'),
                
                Tables\Filters\Filter::make('has_vat')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('vat_number'))
                    ->label('Has VAT Number'),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
