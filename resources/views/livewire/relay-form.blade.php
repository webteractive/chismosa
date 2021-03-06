<x-modal
    :opened="$opened"
    :title="$this->relayId ? 'Edit Relay' : 'New Relay'"
>
    <div class="space-y-4">
        <div>
            <x-label for="name" :value="__('Name')" required />
            <x-input id="name" class="block mt-1 w-full" type="text" wire:model.lazy="relay.name" />
            <x-validation-error for="relay.name" />
        </div>

        <div>
            <x-label for="type" :value="__('Type')" required />
            <x-select id="type" class="block mt-1 w-full" wire:model.lazy="relay.type">
                <option value="">{{ __('Select') }}</option>
                <option value="forge">Forge</option>
            </x-select>
            <x-validation-error for="relay.type" />
        </div>

        <div>
            <x-label for="webhook_type" :value="__('Webhook Type')" required />
            <x-select id="webhook_type" class="block mt-1 w-full" wire:model.lazy="relay.webhook_type">
                <option value="">{{ __('Select') }}</option>
                <option value="google_chat">Google Chat</option>
            </x-select>
            <x-validation-error for="relay.webhook_type" />
        </div>

        <div>
            <x-label for="webhook_url" :value="__('Webhook URL')" required />
            <x-textarea id="webhook_url" class="block mt-1 w-full" wire:model.lazy="relay.webhook_url" />
            <x-validation-error for="relay.webhook_url" />
        </div>

        <div>
            <x-label for="description" :value="__('Description')" />
            <x-textarea id="description" class="block mt-1 w-full" wire:model.lazy="relay.description" />
            <x-validation-error for="relay.description" />
        </div>
    </div>

    <div class="mt-6">
        <x-button wire:click="save">{{ __($this->relayId ? 'Save Changes' : 'Save') }}</x-button>
        <x-button wire:click="close">{{ __('Close') }}</x-button>
    </div>
</x-modal>
