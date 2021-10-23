<x-modal
    :opened="$opened"
    :title="__(':name Logs', ['name' => optional($this->relay)->name])"
    size="w-full md:w-[720px]"
>
    @if ($this->relay)
        <div class="logs -mx-4">
            @foreach ($this->logs as $item)
                <div class="border-b py-2 px-4 flex hover:bg-gray-50">
                    <div class="flex-0">{{ $item->created_at->toDateTimeString() }}</div>
                    <div class="flex-1 ml-4 break-all font-mono">{{ json_encode($item->payload) }}</div>
                </div>
            @endforeach
        </div>

        @if ($this->logs->hasPages())
            <div class="mt-2">
                {{ $this->logs->links() }}
            </div>
        @endif

        <div class="mt-6">
            <x-button wire:click="close">{{ __('Close') }}</x-button>
        </div>
    @endif
</x-modal>