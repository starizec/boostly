<?php

namespace App\Filament\Resources\ChatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $recordTitleAttribute = 'message';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options([
                        'user' => 'User',
                        'agent' => 'Agent',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_read')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'user' => 'info',
                        'agent' => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_read')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'user' => 'User',
                        'agent' => 'Agent',
                    ]),
                Tables\Filters\TernaryFilter::make('is_read'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = 'agent';
                        return $data;
                    }),
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
            ->poll('5s')
            ->reorderable(false)
            ->defaultSort('created_at', 'desc')
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->paginated([25, 50, 100]);
    }

    protected function getTablePollingInterval(): ?string
    {
        return '5s';
    }
} 