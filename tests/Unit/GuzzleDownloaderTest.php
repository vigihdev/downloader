<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Tests\Unit;

use Vigihdev\Downloader\GuzzleDownloader;
use Vigihdev\Downloader\Results\DownloadResult;
use Vigihdev\Downloader\Results\FileInfo;
use Vigihdev\Downloader\Tests\TestCase;

class GuzzleDownloaderTest extends TestCase
{
    private GuzzleDownloader $downloader;

    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->downloader = new GuzzleDownloader();
        $this->testDir = sys_get_temp_dir() . "/guzzle-downloader-test-" . uniqid();

        mkdir($this->testDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->testDir)) {
            array_map("unlink", glob($this->testDir . "/*"));
            rmdir($this->testDir);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_validates_invalid_url(): void
    {
        $result = $this->downloader->download("not-a-valid-url", $this->testDir . "/test.jpg");

        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->getError());
    }

    /** @test */
    public function it_returns_failure_result_when_url_is_invalid(): void
    {
        $url = "not-a-valid-url";
        $path = $this->testDir . "/test.jpg";

        $result = $this->downloader->download($url, $path);

        $this->assertInstanceOf(DownloadResult::class, $result);
        $this->assertFalse($result->isSuccess());

        $this->assertNotEmpty($result->getError());
    }

    /** @test */
    public function it_validates_unsupported_protocol(): void
    {
        $result = $this->downloader->download("ftp://example.com/file.txt", $this->testDir . "/test.txt");

        $this->assertFalse($result->success);
        $this->assertNotNull($result->error);
    }

    /** @test */
    public function it_checks_url_accessibility(): void
    {
        $accessible = $this->downloader->isAccessible(
            "https://httpbin.org/status/200"
        );

        $this->assertIsBool($accessible);
    }

    /** @test */
    public function it_gets_file_info(): void
    {
        $fileInfo = $this->downloader->getFileInfo(
            "https://httpbin.org/image/png"
        );

        $this->assertInstanceOf(FileInfo::class, $fileInfo);
    }

    /** @test */
    public function it_handles_guzzle_specific_features(): void
    {
        $result = $this->downloader->download(
            "https://httpbin.org/image/png",
            $this->testDir . "/test.png"
        );

        $this->assertInstanceOf(DownloadResult::class, $result);
    }

    /** @test */
    public function it_fails_for_non_image_content(): void
    {
        $this->expectException(\Vigihdev\Downloader\Exceptions\DownloadException::class);

        $this->downloader->download(
            "https://httpbin.org/json", // This returns JSON, not an image
            $this->testDir . "/test.jpg"
        );
    }

    /** @test */
    public function it_accepts_custom_config(): void
    {
        $downloader = new GuzzleDownloader([
            "timeout" => 10,
            "connect_timeout" => 5,
            "verify" => false
        ]);

        $this->assertInstanceOf(GuzzleDownloader::class, $downloader);
    }
}
