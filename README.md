# Chismosa by Webteractive

A webhook relay application that receives webhooks from a service and forwards them as formatted messages to a chat destination. Today it relays Laravel Forge deployment notifications to Google Chat.

## Features

- Create and manage webhook relays
- Laravel Forge deployment webhooks relayed to Google Chat as cards
- A shared relay key that authenticates every relay endpoint and can be rotated without recreating relays
- Request logging, purged weekly once older than a month
- Outbound messages sent from the queue, with retries, supervised by Laravel Horizon
- Admin panel for relays, logs, users and the relay key

## How it works

1. A service calls `/relay/{id}/{key}`.
2. The request is rejected with a 404 unless the relay is active and the key matches the current relay key.
3. The payload is stored as a relay log and a `SendRelayMessage` job is queued.
4. A Horizon worker posts the message to the relay's webhook URL. A failed delivery is retried up to three times (after 10 and then 60 seconds) before landing in `failed_jobs`.

## Tech Stack

- **PHP** 8.4
- **Laravel** 13
- **Filament** 5
- **Livewire** 4
- **Horizon** 5 on Redis
- **Pest** 5 (testing)

There is no frontend build step. The admin panel uses Filament's own published assets.

## Development Setup

### Prerequisites

- PHP 8.3+
- Composer
- Redis, with the `redis` PHP extension
- [Laravel Herd](https://herd.laravel.com) (recommended)

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd chismosa

# Install dependencies, create .env, generate the app key and migrate
composer setup
```

Then in `.env`:

- Set `CHISMOSA_ADMIN_PATH` to the path the admin panel should be served from.
- Set `QUEUE_CONNECTION=redis` so jobs go to the queue Horizon supervises.

### Running Locally

With Laravel Herd, the app is automatically available at `https://chismosa.test`.

Messages are only delivered while Horizon is running:

```bash
php artisan horizon
```

The Horizon dashboard is at `/<CHISMOSA_ADMIN_PATH>/horizon` and is linked from the admin panel. Anyone who can sign in to the admin panel can view it.

### Horizon and shared Redis servers

Chismosa is safe to run next to other Horizon applications on the same Redis server. Its Horizon keys live under `chismosa_horizon:` and its queue keys under `chismosa-database-`, both fixed rather than derived from `APP_NAME`, so another app with the same name cannot pick up its jobs. `php artisan horizon:terminate` only stops Chismosa's own Horizon. Override the prefixes with `HORIZON_PREFIX` and `REDIS_PREFIX` if two copies of Chismosa ever share one Redis server.

### Production

- Run `php artisan horizon` under a process monitor (a Forge daemon or Supervisor).
- Run `php artisan horizon:terminate` in the deploy script so workers pick up new code.
- Run the scheduler. It purges old relay logs and records Horizon metrics.

### Running Tests

```bash
composer test
```

### Code Formatting

```bash
vendor/bin/pint
```
