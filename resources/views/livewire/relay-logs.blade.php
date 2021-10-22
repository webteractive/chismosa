<x-modal
    :opened="$opened"
    :title="__(':name Logs', ['name' => optional($this->relay)->name])"
    size="container"
>
    <div class="logs">
        @foreach ($this->logs as $item)
            <pre>{{ json_encode($item) }}</pre>
        @endforeach
    </div>

    <div class="mt-6">
        <x-button wire:click="close">{{ __('Close') }}</x-button>
    </div>
</x-modal>