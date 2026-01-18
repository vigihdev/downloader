<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vigihdev\Downloader\ImageDownloader;
use Vigihdev\Downloader\Contracts\ProviderInterface;
use Vigihdev\Downloader\Contracts\HttpClientInterface;
use Vigihdev\Downloader\Results\DownloadResult;
use Vigihdev\Downloader\Results\ResponseHeader;
use Vigihdev\Downloader\Exceptions\DownloadException;
use Symfony\Component\Filesystem\Filesystem;

class ImageDownloaderTest extends TestCase
{
    /**
     * @var MockObject|ProviderInterface $provider
     */
    private ProviderInterface $provider;
    /**
     * @var MockObject|HttpClientInterface $httpClient
     */
    private HttpClientInterface $httpClient;
    private Filesystem $filesystem;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . '/image_downloader_test_' . uniqid();
        $this->filesystem->mkdir($this->tempDir);

        $this->provider = $this->createMock(ProviderInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->filesystem->exists($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }
    }

    public function testSuccessfulDownload(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/image.jpg';
        $imageContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQECAQECAQEBAgMDAwMDBgQEAwUHBgcHBwYGBgcICwkHCAoIBgYJDQkKCwsMDAwHCQ0RDgsLEAwMEP/2wBDAQEBAQEBAQICAgIEAgIEAwMDAwMEBAQDBAUFBgUHBwgICAcICgwJBwgKDAsLCwwMCQwNDhAQDAsQERITEhMTChMREhP/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/gA=='); // Simple fake JPEG

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(false);
        $this->provider->method('maxFileSize')->willReturn(1024 * 1024); // 1MB

        $this->httpClient->method('get')->willReturn($imageContent);

        $downloader = new ImageDownloader($this->provider, $this->httpClient);
        $result = $downloader->download();

        $this->assertInstanceOf(DownloadResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($destination, $result->getDestination());
        $this->assertEquals(strlen($imageContent), $result->getSize());
        $this->assertNotNull($result->getMetadata());
        $this->assertEquals($url, $result->getMetadata()->url);
    }

    public function testSuccessfulDownloadWithHeaderHint(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/image_with_header.jpg';
        $imageContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQECAQECAQEBAgMDAwMDBgQEAwUHBgcHBwYGBgcICwkHCAoIBgYJDQkKCwsMDAwHCQ0RDgsLEAwMEP/2wBDAQEBAQEBAQICAgIEAgIEAwMDAwMEBAQDBAUFBgUHBwgICAcICgwJBwgKDAsLCwwMCQwNDhAQDAsQERITEhMTChMREhP/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/gA=='); // Simple fake JPEG
        $contentType = 'image/jpeg';
        $contentLength = strlen($imageContent);

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(false);
        $this->provider->method('maxFileSize')->willReturn(1024 * 1024); // 1MB

        $responseHeader = new ResponseHeader([
            'content-type' => [$contentType],
            'content-length' => [$contentLength]
        ]);

        $this->httpClient->method('getHeaders')->willReturn($responseHeader);
        $this->httpClient->method('get')->willReturn($imageContent);

        $downloader = new ImageDownloader($this->provider, $this->httpClient, true);
        $result = $downloader->download();

        $this->assertInstanceOf(DownloadResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($destination, $result->getDestination());
    }

    public function testDownloadThrowsExceptionWhenFileAlreadyExistsAndNotAllowedToOverwrite(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/existing_image.jpg';

        // Create a file that already exists
        $this->filesystem->dumpFile($destination, 'existing content');

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(false);
        $this->provider->method('maxFileSize')->willReturn(1024 * 1024); // 1MB

        $downloader = new ImageDownloader($this->provider, $this->httpClient);

        $this->expectException(DownloadException::class);
        $this->expectExceptionMessage('File already exist: ' . $destination);

        $downloader->download();
    }

    public function testDownloadAllowsOverwritingExistingFile(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/existing_image.jpg';
        $imageContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQECAQECAQEBAgMDAwMDBgQEAwUHBgcHBwYGBgcICwkHCAoIBgYJDQkKCwsMDAwHCQ0RDgsLEAwMEP/2wBDAQEBAQEBAQICAgIEAgIEAwMDAwMEBAQDBAUFBgUHBwgICAcICgwJBwgKDAsLCwwMCQwNDhAQDAsQERITEhMTChMREhP/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/gA=='); // Simple fake JPEG

        // Create a file that already exists
        $this->filesystem->dumpFile($destination, 'existing content');

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(true);
        $this->provider->method('maxFileSize')->willReturn(1024 * 1024); // 1MB

        $this->httpClient->method('get')->willReturn($imageContent);

        $downloader = new ImageDownloader($this->provider, $this->httpClient);
        $result = $downloader->download();

        $this->assertInstanceOf(DownloadResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($destination, $result->getDestination());
    }

    public function testDownloadFailsWhenFileExceedsMaxSize(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/image.jpg';
        $imageContent = str_repeat('a', 1024 * 1024 + 1); // 1MB + 1 byte, exceeding limit

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(false);
        $this->provider->method('maxFileSize')->willReturn(1024 * 1024); // 1MB

        $this->httpClient->method('get')->willReturn($imageContent);

        $downloader = new ImageDownloader($this->provider, $this->httpClient);

        $this->expectException(DownloadException::class);

        $downloader->download();
    }

    public function testDownloadFailsWhenHeaderHintExceedsMaxSize(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/image.jpg';
        $imageContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQECAQECAQEBAgMDAwMDBgQEAwUHBgcHBwYGBgcICwkHCAoIBgYJDQkKCwsMDAwHCQ0RDgsLEAwMEP/2wBDAQEBAQEBAQICAgIEAgIEAwMDAwMEBAQDBAUFBgUHBwgICAcICgwJBwgKDAsLCwwMCQwNDhAQDAsQERITEhMTChMREhP/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/gA=='); // Simple fake JPEG
        $contentType = 'image/jpeg';
        $contentLength = 1024 * 1024 + 1; // Exceeds max size

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(false);
        $this->provider->method('maxFileSize')->willReturn(1024 * 1024); // 1MB

        $responseHeader = new ResponseHeader([
            'content-type' => [$contentType],
            'content-length' => [$contentLength]
        ]);

        $this->httpClient->method('getHeaders')->willReturn($responseHeader);
        $this->httpClient->method('get')->willReturn($imageContent);

        $downloader = new ImageDownloader($this->provider, $this->httpClient, true);

        $this->expectException(DownloadException::class);

        $downloader->download();
    }

    public function testDownloadCleansUpFileOnFailure(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/image.jpg';
        $imageContent = str_repeat('a', 100); // Some content

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(false);
        $this->provider->method('maxFileSize')->willReturn(50); // Very small limit to trigger failure

        $this->httpClient->method('get')->willReturn($imageContent);

        $downloader = new ImageDownloader($this->provider, $this->httpClient);

        try {
            $downloader->download();
            $this->fail('Expected DownloadException was not thrown');
        } catch (DownloadException $e) {
            // Verify that the file was cleaned up after failure
            $this->assertFalse(!file_exists($destination));
        }
    }

    public function testDownloadFailsWhenHttpClientThrowsException(): void
    {
        $url = 'https://example.com/image.jpg';
        $destination = $this->tempDir . '/image.jpg';

        $this->provider->method('getUrl')->willReturn($url);
        $this->provider->method('getDestination')->willReturn($destination);
        $this->provider->method('allowOverwrite')->willReturn(false);
        $this->provider->method('maxFileSize')->willReturn(1024 * 1024); // 1MB

        $this->httpClient->method('get')->willThrowException(
            new DownloadException('Network error')
        );

        $downloader = new ImageDownloader($this->provider, $this->httpClient);

        $this->expectException(DownloadException::class);
        $this->expectExceptionMessage('Network error');

        $downloader->download();
    }
}
