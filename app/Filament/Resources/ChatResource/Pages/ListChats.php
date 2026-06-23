<?php

namespace App\Filament\Resources\ChatResource\Pages;

use App\Filament\Authorization\ResourceAccess;
use App\Filament\Resources\ChatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChats extends ListRecords
{
    protected static string $resource = ChatResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return ResourceAccess::canAccessChatList();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
