<?php

namespace App\Filament\Resources\Relays\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Actions\ManageRelayKeyAction;
use App\Filament\Resources\Relays\RelayResource;

class ManageRelays extends ManageRecords
{
    protected static string $resource = RelayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ManageRelayKeyAction::make(),
            Actions\CreateAction::make(),
        ];
    }
}
