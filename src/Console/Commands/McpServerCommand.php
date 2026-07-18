<?php

declare(strict_types=1);

namespace SmsCatcher\Console\Commands;

use Illuminate\Console\Command;
use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;
use SmsCatcher\Storage\MessageRepository;

class McpServerCommand extends Command
{
    protected $signature = 'sms-catcher:mcp';

    protected $description = 'Start the SMS Catcher MCP server for AI agent integration (stdio transport)';

    public function __construct(protected MessageRepository $repository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $repository = $this->repository;

        $server = Server::make()
            ->withServerInfo('SMS Catcher', '1.0.0')
            ->withTool(
                function () use ($repository): array {
                    return $repository->all()->values()->toArray();
                },
                name: 'list_messages',
                description: 'List all captured SMS messages sorted newest-first. Each message has: id (UUID), to (recipient phone number), from (sender, may be null), body (SMS text content), notification (PHP notification class name), timestamp (ISO 8601), read_at (ISO 8601 or null), extra (additional payload fields).'
            )
            ->withTool(
                function (string $id) use ($repository): ?array {
                    return $repository->markAsRead($id);
                },
                name: 'get_message',
                description: 'Get a single captured SMS message by its UUID and mark it as read. Returns null if not found.'
            )
            ->withTool(
                function (string $id) use ($repository): void {
                    $repository->delete($id);
                },
                name: 'delete_message',
                description: 'Permanently delete one captured SMS message by its UUID.'
            )
            ->withTool(
                function () use ($repository): void {
                    $repository->clear();
                },
                name: 'clear_messages',
                description: 'Permanently delete all captured SMS messages.'
            )
            ->build();

        $transport = new StdioServerTransport;
        $server->listen($transport);

        return self::SUCCESS;
    }
}
