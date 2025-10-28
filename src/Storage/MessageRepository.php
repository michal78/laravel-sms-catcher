<?php

namespace SmsCatcher\Storage;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;
use JsonSerializable;
use ReflectionException;
use ReflectionProperty;
use Stringable;
use Throwable;

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
            return $this->normalizeArrayPayload($smsMessage);
        }

        if (is_object($smsMessage)) {
            if ($smsMessage instanceof Arrayable) {
                $normalized = $this->normalizeArrayPayload($smsMessage->toArray());

                if ($normalized !== null) {
                    return $normalized;
                }
            }

            if ($smsMessage instanceof JsonSerializable) {
                $serialized = $smsMessage->jsonSerialize();

                if (is_string($serialized)) {
                    return ['body' => $serialized];
                }

                if (is_array($serialized)) {
                    $normalized = $this->normalizeArrayPayload($serialized);

                    if ($normalized !== null) {
                        return $normalized;
                    }
                }
            }

            $vars = get_object_vars($smsMessage);
            if (!empty($vars)) {
                $normalized = $this->normalizeArrayPayload($vars);

                if ($normalized !== null) {
                    return $normalized;
                }
            }

            $castVars = $this->extractObjectVariables($smsMessage);
            if (!empty($castVars)) {
                $normalized = $this->normalizeArrayPayload($castVars);

                if ($normalized !== null) {
                    return $normalized;
                }
            }

            $body = $this->readPropertyValue($smsMessage, ['body', 'text', 'content', 'contents', 'message']);

            if ($body !== null) {
                $normalized = ['body' => $body];

                $from = $this->readPropertyValue($smsMessage, ['from', 'sender', 'originator']);

                if ($from !== null) {
                    $normalized['from'] = $from;
                }

                return $normalized;
            }
        }

        return null;
    }

    protected function normalizeArrayPayload(array $payload): ?array
    {
        $payload = $this->sanitizeObjectVars($payload);

        $body = $this->extractStringFromArray($payload, ['body', 'text', 'content', 'contents', 'message']);

        if ($body === null) {
            return null;
        }

        foreach (['body', 'text', 'content', 'contents', 'message'] as $key) {
            unset($payload[$key]);
        }

        $normalized = ['body' => $body];

        $from = $this->extractStringFromArray($payload, ['from', 'sender', 'originator']);

        if ($from !== null) {
            foreach (['from', 'sender', 'originator'] as $key) {
                unset($payload[$key]);
            }

            $normalized['from'] = $from;
        }

        $extra = array_filter($payload, fn ($value) => $value !== null);

        if (!empty($extra)) {
            $normalized['extra'] = $extra;
        }

        return $normalized;
    }

    protected function extractStringFromArray(array $payload, array $candidates): ?string
    {
        foreach ($candidates as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $string = $this->stringify($payload[$key]);

            if ($string !== null) {
                return $string;
            }
        }

        return null;
    }

    protected function stringify(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractObjectVariables(object $smsMessage): array
    {
        try {
            return $this->sanitizeObjectVars((array) $smsMessage);
        } catch (Throwable) {
            return [];
        }
    }

    protected function sanitizeObjectVars(array $vars): array
    {
        $sanitized = [];

        foreach ($vars as $key => $value) {
            if (is_string($key)) {
                $key = preg_replace('/^\x00.*\x00/', '', $key);
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    protected function readPropertyValue(object $smsMessage, array $properties): ?string
    {
        foreach ($properties as $property) {
            if (!property_exists($smsMessage, $property)) {
                continue;
            }

            try {
                $reflection = new ReflectionProperty($smsMessage, $property);

                if (!$reflection->isPublic()) {
                    $reflection->setAccessible(true);
                }

                if (method_exists($reflection, 'isInitialized') && !$reflection->isInitialized($smsMessage)) {
                    continue;
                }

                $value = $reflection->getValue($smsMessage);
            } catch (ReflectionException|Throwable) {
                continue;
            }

            $string = $this->stringify($value);

            if ($string !== null) {
                return $string;
            }
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
