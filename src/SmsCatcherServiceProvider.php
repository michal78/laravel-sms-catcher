<?php

namespace SmsCatcher;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SmsCatcher\Http\Controllers\SmsMessageController;
use SmsCatcher\Storage\MessageRepository;

class SmsCatcherServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sms-catcher.php', 'sms-catcher');

        $this->app->singleton(MessageRepository::class, function ($app) {
            return new MessageRepository(config('sms-catcher.storage_path'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(Dispatcher $events): void
    {
        $this->publishes([
            __DIR__ . '/../config/sms-catcher.php' => config_path('sms-catcher.php'),
        ], 'sms-catcher-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sms-catcher');

        if (!config('sms-catcher.enabled')) {
            return;
        }

        $this->registerRoutes();
        $this->registerListeners($events);
    }

    protected function registerRoutes(): void
    {
        Route::group(config('sms-catcher.route'), function () {
            Route::get('/', [SmsMessageController::class, 'index'])->name('sms-catcher.index');
            Route::get('/api/messages', [SmsMessageController::class, 'api'])->name('sms-catcher.api');
            Route::get('/messages/{id}', [SmsMessageController::class, 'show'])->name('sms-catcher.show');
            Route::delete('/messages/{id}', [SmsMessageController::class, 'destroy'])->name('sms-catcher.destroy');
            Route::delete('/', [SmsMessageController::class, 'clear'])->name('sms-catcher.clear');
        });
    }

    protected function registerListeners(Dispatcher $events): void
    {
        $events->listen(NotificationSending::class, function (NotificationSending $event) {
            if ($event->channel !== 'sms') {
                return;
            }

            /** @var MessageRepository $repository */
            $repository = $this->app->make(MessageRepository::class);
            $repository->storeFromEvent($event);
            
            // Return false to prevent the SMS from being actually sent
            return false;
        });
    }
}
