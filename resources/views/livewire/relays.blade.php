<div id="relays">
    <div class="flex justify-end">
        <x-button wire:click="$emitTo('relay-form', 'create')">{{ __('New Relay') }}</x-button>
    </div>
    
    <div class="grid grid-cols-4 gap-4">
        @foreach ($this->relays as $relay)
            <div wire:key="relay-{{ $relay->id }}" class="rounded-lg shadow relative bg-gray-100">
                <div class="p-4 bg-white border-b rounded-t-lg">
                    <h2 class="text-xl font-bold leading-none">{{ $relay->name }}</h2>

                    <div class="space-y-3 mt-5">
                        @if ($relay->description)
                            <p class="text-sm leading-tight">{{ $relay->description }}</p>
                        @endif
                        
                        <div>
                            <div>{{ __('Messages received from') }}</div>
                            <div
                                class="leading-none text-sm text-gray-600 hover:underline"
                                x-data="clipboard"
                                data-clipboard-text="{{ $relay->endpoint }}"
                            >{{ $relay->endpoint }}</div>
                        </div>

                        <div>
                            <div>{{ __('Will be relayed to') }}</div>
                            <div class="leading-none text-sm text-gray-600">{{ $relay->webhook_url }}</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-xs">{{ __('Last updated :date', ['date' => $relay->updated_at->diffForHumans()]) }}</p>
                    </div>
                </div>

                <div class="bg-gray-100 rounded-b-lg p-4 space-x-2">
                    <button
                        type="button"
                        class="text-blue-500 hover:underline"
                        wire:click="$emitTo('relay-form', 'edit', {{ $relay->id }})"
                    >{{ __('Edit') }}</button>

                    <button
                        type="button"
                        class="text-blue-500 hover:underline"
                        wire:click="$emitTo('relay-logs', 'viewLogs', {{ $relay->id }})"
                    >{{ __('View Logs') }}</button>
                </div>
            </div>
        @endforeach
    </div>
</div>
