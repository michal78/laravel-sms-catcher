<?php

declare(strict_types=1);

namespace Tests\Console;

use Illuminate\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase;
use SmsCatcher\SmsCatcherServiceProvider;

class McpServerCommandDisabledTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SmsCatcherServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sms-catcher.enabled', false);
        $app['config']->set('sms-catcher.storage_path', sys_get_temp_dir().'/sms-catcher-test-mcp-disabled.json');
    }

    public function test_command_is_not_registered_when_disabled(): void
    {
        $commands = $this->app->make(Kernel::class)->all();

        $this->assertArrayNotHasKey('sms-catcher:mcp', $commands);
    }
}
