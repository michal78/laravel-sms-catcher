<?php

declare(strict_types=1);

namespace Tests\Storage;

use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;
use SmsCatcher\Storage\MessageRepository;

class MessageRepositoryTest extends TestCase
{
    private string $storagePath;

    protected function setUp(): void
    {
        parent::setUp();

        $directory = sys_get_temp_dir() . '/sms-catcher-tests-' . uniqid('', true);
        $this->storagePath = $directory . '/messages.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->storagePath)) {
            @unlink($this->storagePath);
        }

        $directory = dirname($this->storagePath);

        if (is_dir($directory)) {
            @rmdir($directory);
        }

        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_store_from_event_persists_normalized_message(): void
    {
        $repository = new MessageRepository($this->storagePath);

        Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00', 'UTC'));

        $notifiable = new class {
            public string $name = 'Tester';

            public function routeNotificationFor(string $channel): ?string
            {
                return $channel === 'sms' ? '+15550000001' : null;
            }
        };

        $notification = new class extends Notification {
            public function toSms($notifiable): array
            {
                return [
                    'body' => 'Hello ' . $notifiable->name,
                    'from' => 'App',
                    'meta' => ['foo' => 'bar'],
                ];
            }
        };

        $event = new NotificationSending($notifiable, $notification, 'sms');

        $repository->storeFromEvent($event);

        $messages = $repository->all();

        $this->assertCount(1, $messages);

        $message = $messages->first();

        $this->assertArrayHasKey('id', $message);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $message['id']);
        $this->assertSame('+15550000001', $message['to']);
        $this->assertSame('App', $message['from']);
        $this->assertSame('Hello Tester', $message['body']);
        $this->assertSame($notification::class, $message['notification']);
        $this->assertSame(Carbon::now()->toIso8601String(), $message['timestamp']);
        $this->assertSame(['meta' => ['foo' => 'bar']], $message['extra']);
    }

    public function test_store_from_event_falls_back_to_phone_property(): void
    {
        $repository = new MessageRepository($this->storagePath);

        Carbon::setTestNow(Carbon::parse('2024-01-01 12:05:00', 'UTC'));

        $notifiable = new class {
            public string $phone = '+15550000002';
        };

        $notification = new class extends Notification {
            public function toSms($notifiable): string
            {
                return 'Fallback Test';
            }
        };

        $repository->storeFromEvent(new NotificationSending($notifiable, $notification, 'sms'));

        $messages = $repository->all();

        $this->assertCount(1, $messages);

        $message = $messages->first();

        $this->assertSame('+15550000002', $message['to']);
        $this->assertSame('Fallback Test', $message['body']);
        $this->assertNull($message['from']);
    }

    public function test_delete_and_clear_remove_messages(): void
    {
        $repository = new MessageRepository($this->storagePath);

        $notifiable = new class {
            public string $phone = '+15550000003';
        };

        $notification = new class extends Notification {
            public function toSms($notifiable): string
            {
                return 'Message ' . $notifiable->phone;
            }
        };

        Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00', 'UTC'));
        $repository->storeFromEvent(new NotificationSending($notifiable, $notification, 'sms'));

        Carbon::setTestNow(Carbon::parse('2024-01-01 12:01:00', 'UTC'));
        $notifiable->phone = '+15550000004';
        $repository->storeFromEvent(new NotificationSending($notifiable, $notification, 'sms'));

        $messages = $repository->all();
        $this->assertCount(2, $messages);
        $this->assertSame('Message +15550000004', $messages->first()['body']);

        $repository->delete($messages->first()['id']);

        $remaining = $repository->all();
        $this->assertCount(1, $remaining);
        $this->assertSame('Message +15550000003', $remaining->first()['body']);

        $repository->clear();

        $this->assertCount(0, $repository->all());
    }
}
