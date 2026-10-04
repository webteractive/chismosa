@use('Carbon\CarbonInterface')

<x-standalone title="Credentials">
    <div id="credentials">
        <h1>Credentials</h1>
        <p class="muted">
            This page works once. It stops working when you close it, or in
            <span id="expires-in">{{ $expiresAt->diffForHumans(now(), CarbonInterface::DIFF_ABSOLUTE) }}</span>,
            whichever comes first.
        </p>

        @if ($relayKey)
            <div class="field">
                <label for="relay-key">Relay key</label>
                <div class="row">
                    <input id="relay-key" type="text" readonly value="{{ $relayKey }}">
                    <button type="button" data-copy="relay-key">Copy</button>
                </div>
            </div>
        @elseif (! $showsWebhookUrls)
            <p>No relay key is set yet, so relays have no endpoint. Rotate the relay key to create one.</p>
        @endif

        @foreach ($relays as $relay)
            <h2>{{ $relay->name }}</h2>

            <div class="field">
                <label for="endpoint-{{ $relay->id }}">Endpoint to give {{ config('chismosa.services')[$relay->type] ?? $relay->type }}</label>
                <div class="row">
                    <input id="endpoint-{{ $relay->id }}" type="text" readonly value="{{ $relay->endpoint ?? 'Set a relay key first' }}">
                    <button type="button" data-copy="endpoint-{{ $relay->id }}">Copy</button>
                </div>
            </div>

            @if ($showsWebhookUrls)
                <div class="field">
                    <label for="webhook-{{ $relay->id }}">Destination webhook URL</label>
                    <div class="row">
                        <input id="webhook-{{ $relay->id }}" type="text" readonly value="{{ $relay->webhook_url }}">
                        <button type="button" data-copy="webhook-{{ $relay->id }}">Copy</button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <script>
        document.querySelectorAll('[data-copy]').forEach(function (button) {
            button.addEventListener('click', function () {
                navigator.clipboard.writeText(document.getElementById(button.dataset.copy).value).then(function () {
                    button.textContent = 'Copied';
                    setTimeout(function () { button.textContent = 'Copy'; }, 1500);
                });
            });
        });

        // Discard the link as soon as the page goes away.
        window.addEventListener('pagehide', function () {
            fetch(@js(route('credentials.destroy', ['reveal' => $reveal])), {
                method: 'DELETE',
                keepalive: true,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
        });

        // Count down to the expiry, and clear the page when the link expires while it is still open.
        var expiresAt = {{ $expiresAt->getTimestampMs() }};
        var countdown = setInterval(showTimeLeft, 1000);

        function showTimeLeft() {
            var secondsLeft = Math.ceil((expiresAt - Date.now()) / 1000);

            if (secondsLeft <= 0) {
                clearInterval(countdown);
                document.getElementById('credentials').innerHTML = '<h1>This link has expired</h1><p class="muted">Ask for a new credentials link.</p>';

                return;
            }

            var minutes = Math.floor(secondsLeft / 60);

            document.getElementById('expires-in').textContent = (minutes ? minutes + ' min ' : '') + (secondsLeft % 60) + ' sec';
        }

        showTimeLeft();
    </script>
</x-standalone>
