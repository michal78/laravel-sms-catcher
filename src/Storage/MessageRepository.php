<?php

namespace SmsCatcher\Storage;

use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;

class MessageRepository
{
    public function __construct(protected string $path)
    {
        $directory = dirname($this->path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function all(): Collection
    {
        $messages = $this->read();

        return collect($messages)->sortByDesc('timestamp')->values();
    }

    public function find(string $id): ?array
    {
        foreach ($this->read() as $message) {
            if ($message['id'] === $id) {
                return $message;
            }
        }

        return null;
    }

    public function delete(string $id): void
    {
        $messages = array_values(array_filter($this->read(), fn ($message) => $message['id'] !== $id));
        $this->write($messages);
    }

    public function clear(): void
    {
        $this->write([]);
    }

    public function storeFromEvent(NotificationSending $event): void
    {
        $message = $this->resolveMessage($event);

        if ($message === null) {
            return;
        }

        $messages = $this->read();
        $messages[] = $message;

        $this->write($messages);
    }

    protected function resolveMessage(NotificationSending $event): ?array
    {
        $notification = $event->notification;

        if (!method_exists($notification, 'toSms')) {
            return null;
        }

        $smsMessage = $notification->toSms($event->notifiable);

        $content = $this->normalizeMessage($smsMessage);

        if ($content === null) {
            return null;
        }

        return [
            'id' => (string) Str::uuid(),
            'to' => $this->resolveRecipient($event),
            'from' => $content['from'] ?? null,
            'body' => $content['body'],
            'notification' => $notification::class,
            'timestamp' => now()->toIso8601String(),
            'extra' => $content['extra'] ?? [],
        ];
    }

    protected function resolveRecipient(NotificationSending $event): string
    {
        $notifiable = $event->notifiable;

        if (method_exists($notifiable, 'routeNotificationFor')) {
            $phone = $notifiable->routeNotificationFor('sms');
            if ($phone !== null) {
                return $phone;
            }
        }

        return (string) ($notifiable->phone ?? $notifiable->phone_number ?? 'unknown');
    }

    protected function normalizeMessage(mixed $smsMessage): ?array
    {
        if (is_string($smsMessage)) {
            return ['body' => $smsMessage];
        }

        if (is_object($smsMessage) && method_exists($smsMessage, '__toString')) {
            return ['body' => (string) $smsMessage];
        }

        if (is_array($smsMessage)) {
            $body = $smsMessage['body'] ?? $smsMessage['text'] ?? null;

            if ($body === null) {
                return null;
            }

            $normalized = ['body' => $body];

            if (isset($smsMessage['from'])) {
                $normalized['from'] = $smsMessage['from'];
            }

            $normalized['extra'] = collect($smsMessage)->except(['body', 'text', 'from'])->toArray();

            return $normalized;
        }

        if (is_object($smsMessage)) {
            $body = $smsMessage->body ?? $smsMessage->text ?? null;

            if ($body === null) {
                return null;
            }

            $normalized = ['body' => $body];

            if (isset($smsMessage->from)) {
                $normalized['from'] = $smsMessage->from;
            }

            $extra = collect(get_object_vars($smsMessage))->except(['body', 'text', 'from'])->toArray();
            if (!empty($extra)) {
                $normalized['extra'] = $extra;
            }

            return $normalized;
        }

        return null;
    }

    protected function read(): array
    {
        if (!file_exists($this->path)) {
            return [];
        }

        try {
            $contents = file_get_contents($this->path);
            if ($contents === false || $contents === '') {
                return [];
            }

            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }
    }

    protected function write(array $messages): void
    {
        file_put_contents($this->path, json_encode($messages, JSON_PRETTY_PRINT));
    }
}
