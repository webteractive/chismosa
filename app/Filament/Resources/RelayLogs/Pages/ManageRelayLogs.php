<?php

namespace App\Filament\Resources\RelayLogs\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\RelayLogs\RelayLogResource;

class ManageRelayLogs extends ManageRecords
{
    protected static string $resource = RelayLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
