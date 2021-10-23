<div id="relays" class="px-4 sm:px-0">
    <div class="flex justify-end">
        <x-button wire:click="$emitTo('relay-form', 'create')">{{ __('New Relay') }}</x-button>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
        @foreach ($this->relays as $relay)
            @php
                $services = config('chismosa.services')
            @endphp
            <div wire:key="relay-{{ $relay->id }}" class="rounded-lg shadow relative bg-gray-100">
                <div class="p-4 bg-white border-b rounded-t-lg">
                    <h2 class="text-xl font-bold leading-none">{{ $relay->name }}</h2>

                    <div class="space-y-4 mt-5">
                        @if ($relay->description)
                            <p class="text-sm leading-tight">{{ $relay->description }}</p>
                        @endif
                        
                        <div class="space-y-1">
                            <div class="leading-none text-gray-800">
                                <span>{{ __('Messages from') }}</span>
                                <span class="text-black">{{ __($services[$relay->type]) }}</span>
                            </div>
                            <div
                                class="leading-none text-sm text-blue-500 break-all font-mono"
                            >{{ $relay->endpoint }}</div>
                        </div>

                        <div class="space-y-1">
                            <div class="leading-none text-gray-800">
                                <span>{{ __('Will be relayed to') }}</span>
                                <span class="text-black">{{ __($services[$relay->webhook_type]) }}</span>
                            </div>
                            <div
                                class="leading-none text-sm text-blue-500 break-all font-mono"
                            >{{ $relay->webhook_url }}</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-xs">{{ __('Last updated :date', ['date' => $relay->updated_at->diffForHumans()]) }}</p>
                    </div>
                </div>

                <div class="bg-gray-100 rounded-b-lg p-4 flex justify-between items-center">
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
