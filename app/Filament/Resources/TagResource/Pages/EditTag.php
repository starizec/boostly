<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Obriši')
                ->before(function ($record) {
                    if ($record->hasChats()) {
                        throw new \Exception('Ne možete obrisati oznaku koja je povezana s razgovorima. Molimo prvo uklonite oznaku iz svih razgovora.');
                    }
                }),
        ];
    }
}
