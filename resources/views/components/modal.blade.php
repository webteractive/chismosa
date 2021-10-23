@props([
    'opened' => false,
    'title' => null,
    'size' => 'w-[512px]'
])

<div
    class="fixed inset-0 bg-black bg-opacity-25 z-50 flex items-center justify-center {{ $opened ? '' : 'hidden' }}"
>
    @if ($opened)
        <div class="bg-white rounded-lg {{ $size }} max-h-[75vh] overflow-y-auto shadow p-4 space-y-6">
            @if ($title)
                <h2 class="modal-title text-2xl leading-none font-bold">{{ $title }}</h2>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>
        </div>
    @endif
</div>