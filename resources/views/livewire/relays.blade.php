<div id="relays">
    <div class="flex justify-end">
        <x-button wire:click="$emitTo('relay-form', 'create')">{{ __('New Relay') }}</x-button>
    </div>
    
    <div class="grid grid-cols-4 gap-4">
        @foreach ($this->relays as $relay)
            <div wire:key="relay-{{ $relay->id }}" class="bg-white p-4 rounded-lg shadow relative">
                <h2 class="text-xl font-bold leading-none">{{ $relay->name }}</h2>

                @if ($relay->description)
                    <p class="mt-2 text-sm">{{ $relay->description }}</p>
                @endif
                
                <div class="mt-2">
                    <div>{{ __('Messages received from') }}</div>
                    <div
                        class="leading-none text-sm text-gray-600 hover:underline"
                        x-data="clipboard"
                        data-clipboard-text="{{ $relay->endpoint }}"
                    >{{ $relay->endpoint }}</div>
                </div>

                <div class="mt-2">
                    <div>{{ __('Will be relayed to') }}</div>
                    <div class="leading-none text-sm text-gray-600">{{ $relay->webhook_url }}</div>
                </div>

                <div class="mt-6">
                    <p class="text-xs">{{ __('Last updated :date', ['date' => $relay->updated_at->diffForHumans()]) }}</p>
                </div>

                <div class="absolute top-4 right-4">
                    <button wire:click="$emitTo('relay-form', 'edit', {{ $relay->id }})" type="button" class="text-blue-500 hover:underline">{{ __('Edit') }}</button>
                </div>
            </div>
        @endforeach
    </div>
</div>
