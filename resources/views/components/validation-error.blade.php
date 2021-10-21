@props(['for'])

@error($for)
    <p class="text-xs text-red-500">{{ $message }}</p>
@enderror