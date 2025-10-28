# Laravel SMS Catcher

A development-only Laravel package that captures SMS notifications and displays them in a beautiful, phone-inspired inbox – think Mailpit, but for your `sms` notification channel.

## Installation

Require the package in your application as a dev dependency. Until a tagged
release is published you will need to target the `dev-main` branch explicitly:

```bash
composer require --dev michal78/laravel-sms-catcher
```

The package is auto-discovered by Laravel, but you can manually register the service provider if you have discovery disabled:

```php
// config/app.php
'providers' => [
    // ...
    SmsCatcher\SmsCatcherServiceProvider::class,
];
```

## Configuration

By default the dashboard is only enabled when your application is running in the `local` environment or when `APP_DEBUG=true`. You can override this behaviour via the `SMS_CATCHER_ENABLED` environment variable.

Publish the configuration file if you would like to customise the dashboard path or storage location:

```bash
php artisan vendor:publish --tag=sms-catcher-config
```

This will create `config/sms-catcher.php` with the following options:

- `enabled`: Toggle the catcher on/off.
- `route.prefix`: URL prefix for the dashboard (defaults to `/sms-catcher`).
- `route.middleware`: Middleware stack wrapping the dashboard routes.
- `storage_path`: File that stores captured messages.

## Usage

Trigger any Laravel notification that uses the `sms` channel and the payload will be recorded automatically. Visit the dashboard (default at `http://your-app.test/sms-catcher`) to see the inbox:

- Inbox view summarises each message.
- Click a message to view details and a phone-style preview.
- Clear individual messages or wipe the entire inbox.

Messages are stored as JSON within your application's storage folder (`storage/framework/sms-catcher.json`). The file is safe to delete; it will be recreated as new messages arrive.

> **Note**: The catcher inspects notifications by invoking their `toSms` method. Ensure your notifications implement this method and return either a string, array, or object containing the text body.

## Security

This package is intended for local development only. Do not enable it in production environments.
