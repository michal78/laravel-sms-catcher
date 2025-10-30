# Laravel SMS Catcher

A development-only Laravel package that captures SMS notifications and displays them in a beautiful, phone-inspired inbox – think Mailpit, but for your `sms` notification channel.

## Screenshots

### SMS Inbox Dashboard
Click to view full-size images:

<a href="https://github.com/user-attachments/assets/9ef222e5-6a16-4fbb-a032-2558cb9edffa" target="_blank">
  <img width="600" alt="SMS Catcher inbox showing list of captured messages" src="https://github.com/user-attachments/assets/9ef222e5-6a16-4fbb-a032-2558cb9edffa" />
</a>

*The main inbox view displays all captured SMS messages with sender, recipient, and timestamp information.*

### Message Detail View

<a href="https://github.com/user-attachments/assets/be6b171d-9fcb-467c-9093-1f67faac35cf" target="_blank">
  <img width="600" alt="SMS message detail view with phone-style preview" src="https://github.com/user-attachments/assets/be6b171d-9fcb-467c-9093-1f67faac35cf" />
</a>

*Click any message to see the full details in a beautiful phone-style preview, making it easy to verify how your SMS will appear to recipients.*

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
