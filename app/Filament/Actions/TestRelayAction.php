<?php

namespace App\Filament\Actions;

use App\Models\Relay;
use App\Support\Relayer;
use Filament\Actions\Action;
use App\Exceptions\RelayDeliveryFailed;
use Filament\Notifications\Notification;

class TestRelayAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'test';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Send test message')
            ->icon('heroicon-o-beaker')
            ->requiresConfirmation()
            ->modalHeading('Send a test message?')
            ->modalDescription('A sample message marked as a test is sent straight to this relay\'s destination. Nothing is logged.')
            ->modalSubmitActionLabel('Send')
            ->action(function (Relay $record): void {
                try {
                    $sent = Relayer::make($record)->sendTest();
                } catch (RelayDeliveryFailed $exception) {
                    Notification::make()->danger()->title('Test message failed')->body($exception->getMessage())->send();

                    return;
                }

                if (! $sent) {
                    Notification::make()
                        ->warning()
                        ->title('Nothing to test')
                        ->body("{$record->name} has no outgoing message for {$record->type} webhooks.")
                        ->send();

                    return;
                }

                Notification::make()->success()->title('Test message delivered')->send();
            });
    }
}
