<?php

namespace App\Filament\Actions;

use App\Models\RelayLog;
use App\Support\Relayer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ResendRelayLogAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'resend';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Resend')
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->modalHeading('Resend this payload?')
            ->modalDescription('The payload is delivered to the relay\'s destination again, formatted the same way as when it arrived. No new log is created.')
            ->modalSubmitActionLabel('Resend')
            ->action(function (RelayLog $record): void {
                $relay = $record->relay;

                if (! $relay) {
                    Notification::make()->danger()->title('This log\'s relay no longer exists')->send();

                    return;
                }

                if (! Relayer::make($relay)->withPayload($record->payload)->notify()) {
                    Notification::make()
                        ->warning()
                        ->title('Nothing to resend')
                        ->body("{$relay->name} has no outgoing message for {$relay->type} webhooks.")
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title('Payload queued for delivery')
                    ->body("Sending through {$relay->name}.")
                    ->send();
            });
    }
}
