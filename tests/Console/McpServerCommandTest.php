<?php

declare(strict_types=1);

namespace Tests\Console;

use Illuminate\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase;
use SmsCatcher\Console\Commands\McpServerCommand;
use SmsCatcher\SmsCatcherServiceProvider;
use SmsCatcher\Storage\MessageRepository;

class McpServerCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SmsCatcherServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sms-catcher.enabled', true);
        $app['config']->set('sms-catcher.storage_path', sys_get_temp_dir().'/sms-catcher-test-mcp.json');
    }

    public function test_command_has_correct_signature(): void
    {
        $command = new McpServerCommand(
            $this->app->make(MessageRepository::class)
        );

        $this->assertSame('sms-catcher:mcp', $command->getName());
    }

    public function test_command_is_registered_when_enabled(): void
    {
        $commands = $this->app->make(Kernel::class)->all();

        $this->assertArrayHasKey('sms-catcher:mcp', $commands);
    }
}
