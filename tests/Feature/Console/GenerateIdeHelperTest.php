<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature\Console;

use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Tests\TestCase;

class GenerateIdeHelperTest extends TestCase
{
    private string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputPath = sys_get_temp_dir() . '/_ide_helper_fast2sms_test.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->outputPath)) {
            unlink($this->outputPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_generates_ide_helper_file_at_default_path(): void
    {
        $defaultPath = base_path('_ide_helper_fast2sms.php');

        $this->artisan('fast2sms:ide-helper')
            ->assertExitCode(0);

        $this->assertFileExists($defaultPath);

        if (file_exists($defaultPath)) {
            unlink($defaultPath);
        }
    }

    #[Test]
    public function it_generates_ide_helper_file_at_custom_path(): void
    {
        $this->artisan('fast2sms:ide-helper', ['--path' => $this->outputPath])
            ->assertExitCode(0);

        $this->assertFileExists($this->outputPath);
    }

    #[Test]
    public function it_generates_file_with_fast2sms_facade_class(): void
    {
        $this->artisan('fast2sms:ide-helper', ['--path' => $this->outputPath])
            ->assertExitCode(0);

        $contents = file_get_contents($this->outputPath);

        $this->assertStringContainsString('class Fast2sms', $contents);
        $this->assertStringContainsString('namespace Shakil\Fast2sms\Facades', $contents);
    }

    #[Test]
    public function it_outputs_success_message_with_path(): void
    {
        $this->artisan('fast2sms:ide-helper', ['--path' => $this->outputPath])
            ->expectsOutputToContain($this->outputPath)
            ->assertExitCode(0);
    }

    #[Test]
    public function it_generates_file_containing_key_method_stubs(): void
    {
        $this->artisan('fast2sms:ide-helper', ['--path' => $this->outputPath])
            ->assertExitCode(0);

        $contents = file_get_contents($this->outputPath);

        $this->assertStringContainsString('public static function fake()', $contents);
        $this->assertStringContainsString('public static function send()', $contents);
        $this->assertStringContainsString('public static function checkBalance()', $contents);
        $this->assertStringContainsString('public static function viaWhatsApp(', $contents);
    }
}
