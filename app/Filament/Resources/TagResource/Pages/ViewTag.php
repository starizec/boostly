<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\Grid;

class ViewTag extends ViewRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    if ($record->hasChats()) {
                        throw new \Exception('Cannot delete tag that is associated with chats. Please remove the tag from all chats first.');
                    }
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Tag Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Tag Name')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),

                                ColorEntry::make('color')
                                    ->label('Tag Color')
                                    ->size(40),
                            ]),

                        TextEntry::make('description')
                            ->label('Description')
                            ->markdown()
                            ->visible(fn ($record) => !empty($record->description)),
                    ])
                    ->columns(1),

                Section::make('Relationships')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('company.name')
                                    ->label('Company')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('user.name')
                                    ->label('Created By')
                                    ->badge()
                                    ->color('info'),
                            ]),

                        TextEntry::make('chats_count')
                            ->label('Associated Chats')
                            ->badge()
                            ->color('warning')
                            ->state(fn ($record) => $record->chats()->count()),
                    ])
                    ->columns(1),

                Section::make('Timestamps')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
