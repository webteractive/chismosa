# Chismosa by Webteractive

A webhook relay application that receives webhooks from various services and forwards them to configured destinations like Google Chat or Laravel Forge.

## Features

- Create and manage webhook relays
- Support for multiple webhook types (Google Chat, Laravel Forge)
- Secure relay keys for endpoint authentication
- Request logging and monitoring
- Admin panel for relay management

## Tech Stack

- **PHP** 8.4
- **Laravel** 12
- **Filament** 4
- **Livewire** 3
- **Pest** 3 (testing)

## Development Setup

### Prerequisites

- PHP 8.4+
- Composer
- Node.js & npm
- [Laravel Herd](https://herd.laravel.com) (recommended)

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd chismosa

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate

# Build assets
npm run build
```

### Running Locally

With Laravel Herd, the app is automatically available at `https://chismosa.test`.

### Running Tests

```bash
php artisan test
```

### Code Formatting

```bash
vendor/bin/pint
```
