<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class ChatsRelationManager extends RelationManager
{
    protected static string $relationship = 'chats';

    protected static ?string $recordTitleAttribute = 'contact.name';

    protected static ?string $title = 'Razgovori';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('last_message_at')
                    ->label('Zadnja poruka'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contact.name')
            ->columns([
                TextColumn::make('contact.name')
                    ->label('Kontakt')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('widget.name')
                    ->label('Widget')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'aktivan',
                        'warning' => 'na čekanju',
                        'danger' => 'zatvoren',
                        'secondary' => 'arhiviran',
                    ]),

                TextColumn::make('last_message_at')
                    ->label('Zadnja poruka')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktivan' => 'Aktivan',
                        'na čekanju' => 'Na čekanju',
                        'zatvoren' => 'Zatvoren',
                        'arhiviran' => 'Arhiviran',
                    ]),

                Filter::make('has_messages')
                    ->label('Ima poruke')
                    ->query(fn (Builder $query): Builder => $query->whereHas('messages')),

                Filter::make('no_messages')
                    ->label('Nema poruka')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('messages')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Dodaj razgovor')
                    ->preloadRecordSelect()
                    ->recordSelectQuery(fn (Builder $query) => $query->whereDoesntHave('tags', function (Builder $query) {
                        $query->where('tags.id', $this->getOwnerRecord()->id);
                    })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Pregled'),
                Tables\Actions\DetachAction::make()
                    ->label('Ukloni oznaku')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Ukloni oznake'),
                ]),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->searchable()
            ->paginated([10, 25, 50]);
    }
}
