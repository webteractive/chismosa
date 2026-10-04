@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    <style>
        :root {
            color-scheme: light dark;
            --page: #fafaf9;
            --card: #ffffff;
            --text: #1c1917;
            --muted: #78716c;
            --border: #e7e5e4;
            --field: #f5f5f4;
            --accent: #d97706;
            --accent-text: #ffffff;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --page: #0c0a09;
                --card: #1c1917;
                --text: #fafaf9;
                --muted: #a8a29e;
                --border: #292524;
                --field: #0c0a09;
                --accent: #f59e0b;
                --accent-text: #1c1917;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: var(--page);
            color: var(--text);
            font: 15px/1.5 ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        main {
            width: 100%;
            max-width: 34rem;
            padding: 1.75rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
        }

        h1 { margin: 0 0 0.25rem; font-size: 1.25rem; line-height: 1.3; }
        h2 { margin: 1.5rem 0 0.5rem; font-size: 0.95rem; }
        p { margin: 0 0 1rem; }
        .muted { color: var(--muted); font-size: 0.875rem; }

        .field { margin-bottom: 0.75rem; }
        .field label { display: block; margin-bottom: 0.25rem; color: var(--muted); font-size: 0.8125rem; }
        .field .row { display: flex; gap: 0.5rem; }

        .field input {
            flex: 1;
            min-width: 0;
            padding: 0.5rem 0.625rem;
            background: var(--field);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            font: 0.8125rem ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        button {
            padding: 0.5rem 0.875rem;
            background: transparent;
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            font: inherit;
            font-size: 0.875rem;
            cursor: pointer;
        }

        button.primary { background: var(--accent); border-color: var(--accent); color: var(--accent-text); font-weight: 600; }
        button:focus-visible, input:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        .actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
        .actions form { flex: 1; }
        .actions button { width: 100%; padding: 0.625rem; }
    </style>
</head>
<body>
    <main>
        {{ $slot }}
    </main>
</body>
</html>
