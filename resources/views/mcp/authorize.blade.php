<x-standalone title="Authorize {{ $client->name }}">
    <h1>Authorize {{ $client->name }}</h1>
    <p class="muted">Signed in as {{ $user->email }}</p>

    <p>
        {{ $client->name }} is asking to connect to {{ config('app.name') }} as you. It will be able to read and
        manage relays, relay logs and admin accounts, and rotate the relay key.
    </p>

    <p class="muted">Only continue if you started this connection yourself.</p>

    <div class="actions">
        <form method="POST" action="{{ route('passport.authorizations.deny') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit">Cancel</button>
        </form>

        <form method="POST" action="{{ route('passport.authorizations.approve') }}">
            @csrf
            <input type="hidden" name="state" value="">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="primary">Authorize</button>
        </form>
    </div>
</x-standalone>
