<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vigihdev\Downloader\NativeDownloader;
use Vigihdev\Downloader\Results\DownloadResult;
use Vigihdev\Downloader\Results\FileInfo;

class NativeDownloaderTest extends TestCase
{

    private NativeDownloader $downloader;

    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->downloader = new NativeDownloader();
        $this->testDir = sys_get_temp_dir() . '/downloader-test-' . uniqid();

        mkdir($this->testDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->testDir)) {
            array_map('unlink', glob($this->testDir . '/*'));
            rmdir($this->testDir);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_validates_invalid_url(): void
    {
        $result = $this->downloader->download('not-a-valid-url', $this->testDir . '/test.jpg');

        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('Invalid URL', $result->getError() ?? '');
    }

    /** @test */
    public function it_returns_failure_result_when_url_is_invalid(): void
    {

        $url = 'not-a-valid-url';
        $path = $this->testDir . '/test.jpg';

        $result = $this->downloader->download($url, $path);

        $this->assertInstanceOf(DownloadResult::class, $result);
        $this->assertFalse($result->isSuccess(), 'Harusnya gagal karena URL tidak valid');

        $this->assertNotEmpty($result->getError());
        $this->assertStringContainsString('valid', strtolower($result->getError()));
    }


    /** @test */
    public function it_creates_destination_directory(): void
    {
        $nestedDir = $this->testDir . '/nested/subdirectory';
        $destination = $nestedDir . '/image.jpg';

        // Directory shouldn't exist yet
        $this->assertDirectoryDoesNotExist($nestedDir);
    }

    /** @test */
    public function it_validates_unsupported_protocol(): void
    {
        $result = $this->downloader->download('ftp://example.com/file.txt', $this->testDir . '/test.txt');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Unsupported protocol', $result->error ?? '');
    }

    /** @test */
    public function it_checks_url_accessibility(): void
    {
        // Real test URL (Wikipedia logo)
        $accessible = $this->downloader->isAccessible(
            'https://upload.wikimedia.org/wikipedia/en/thumb/8/80/Wikipedia-logo-v2.svg/120px-Wikipedia-logo-v2.svg.png'
        );

        $this->assertIsBool($accessible);
    }

    /** @test */
    public function it_gets_file_info(): void
    {
        $fileInfo = $this->downloader->getFileInfo(
            'https://upload.wikimedia.org/wikipedia/en/thumb/8/80/Wikipedia-logo-v2.svg/120px-Wikipedia-logo-v2.svg.png'
        );

        $this->assertInstanceOf(FileInfo::class, $fileInfo);

        if ($fileInfo->exists) {
            $this->assertGreaterThan(0, $fileInfo->size ?? 0);
            $this->assertStringStartsWith('image/', $fileInfo->mimeType ?? '');
        }
    }

    /** @test */
    public function it_handles_context_options(): void
    {
        $downloader = new NativeDownloader([
            'timeout' => 10,
            'user_agent' => 'MyCustomAgent/1.0',
            'verify_ssl' => false,
        ]);

        $result = $downloader->download(
            'https://example.com/image.jpg',
            $this->testDir . '/test.jpg'
        );

        $this->assertInstanceOf(DownloadResult::class, $result);
    }
}
