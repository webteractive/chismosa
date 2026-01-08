<?php

namespace App\Filament\Actions;

use App\Models\RelayKey;
use Illuminate\Support\Str;
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
                                $ulid1 = (string) Str::ulid();
                                $ulid2 = (string) Str::ulid();
                                $random = Str::random(12);
                                $set('key', $ulid1.$ulid2.$random);
                            })
                    ),
            ])
            ->action(function (array $data): void {
                $record = RelayKey::query()->first();

                if (! $record) {
                    $record = new RelayKey;
                }

                $record->fill($data);
                $record->save();

                Notification::make()
                    ->success()
                    ->title('Relay key saved successfully')
                    ->send();
            });
    }
}
