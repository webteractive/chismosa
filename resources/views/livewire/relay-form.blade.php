<div
    id="relay-form"
    class="fixed inset-0 bg-black bg-opacity-25 z-50 flex items-center justify-center {{ $opened ? '' : 'hidden' }}"
>
    @if ($opened)
        <div class="bg-white rounded-lg w-[512px] min-h-[300px] shadow p-4">
            <h2 class="text-xl leading-none">{{ __($this->relayId ? 'Edit Relay' : 'New Relay') }}</h2>

            <div class="mt-6 space-y-4">
                <div>
                    <x-label for="name" :value="__('Name')" />
                    <x-input id="name" class="block mt-1 w-full" type="text" wire:model.lazy="relay.name" />
                    <x-validation-error for="relay.name" />
                </div>

                <div>
                    <x-label for="description" :value="__('Description')" />
                    <x-textarea id="description" class="block mt-1 w-full" wire:model.lazy="relay.description" />
                    <x-validation-error for="relay.description" />
                </div>

                <div>
                    <x-label for="webhook_url" :value="__('Webhook URL')" />
                    <x-textarea id="webhook_url" class="block mt-1 w-full" wire:model.lazy="relay.webhook_url" />
                    <x-validation-error for="relay.webhook_url" />
                </div>
            </div>

            <div class="mt-6">
                <x-button wire:click="save">{{ __($this->relayId ? 'Save Changes' : 'Save') }}</x-button>
                <x-button wire:click="close">{{ __('Close') }}</x-button>
            </div>
        </div>
    @endif
</div>
