<?php

namespace App\Filament\Actions;

use App\Models\RelayKey;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class ManageRelayKeyAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manageRelayKey';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Manage Relay Key')
            ->icon('heroicon-o-key')
            ->modalHeading('Manage Relay Key')
            ->modalDescription('Update the API key used for relay authentication.')
            ->form([
                TextInput::make('key')
                    ->label('Relay Key')
                    ->required()
                    ->maxLength(255)
                    ->password()
                    ->revealable()
                    ->default(fn () => RelayKey::query()->first()?->key)
                    ->helperText('The API key used for relay authentication. Keep this secure.')
                    ->suffixAction(
                        Action::make('generateKey')
                            ->icon('heroicon-o-arrow-path')
                            ->label('Generate')
                            ->action(function ($set): void {
                                $set('key', RelayKey::generate());
                            })
                    ),
            ])
            ->action(function (array $data): void {
                RelayKey::store($data['key']);

                Notification::make()
                    ->success()
                    ->title('Relay key saved successfully')
                    ->send();
            });
    }
}
