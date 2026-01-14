<?php

declare(strict_types=1);

namespace Vigihdev\Downloader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Vigihdev\Downloader\Exceptions\DownloadException;
use Vigihdev\Downloader\Results\{DownloadResult, FileInfo, MetadataDownload};
use Vigihdev\Downloader\Support\Util;

final class GuzzleDownloader extends BaseDownloader
{
    private const METHOD_NAME = 'guzzle';

    private Client $client;

    private array $defaultConfig = [
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => true,
        'allow_redirects' => [
            'max' => 5,
            'strict' => true,
            'referer' => true,
        ],
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (compatible; MockForge/1.0)',
        ],
    ];

    public function __construct(array $config = [])
    {

        parent::__construct();
        $this->client = new Client(array_merge($this->defaultConfig, $config));
    }

    public function download(string $url, string $destination, array $options = []): DownloadResult
    {
        try {
            $response = $this->client->get($url, $options);

            $contentType = $response->getHeaderLine('Content-Type');
            $contentLength = $response->getHeaderLine('Content-Length');

            // Validate image type
            if (!Util::isImageMimeType($contentType)) {
                throw DownloadException::invalidMimeType($url, $contentType);
            }

            $destination = $this->resolveDestination($url, $destination);

            // Save to file
            file_put_contents($destination, $response->getBody());

            return new DownloadResult(
                success: true,
                destination: $destination,
                size: (int) $contentLength,
                mimeType: $contentType,
                metadata: new MetadataDownload(
                    url: $url,
                    originalSize: (int) $contentLength,
                    downloadTime: date('Y-m-d H:i:s'),
                    method: self::METHOD_NAME,
                ),
            );
        } catch (GuzzleException $e) {
            return new DownloadResult(
                success: false,
                destination: $destination,
                size: 0,
                mimeType: '',
                error: $e->getMessage(),
                metadata: new MetadataDownload(
                    url: $url,
                    originalSize: 0,
                    downloadTime: date('Y-m-d H:i:s'),
                    method: self::METHOD_NAME,
                ),
            );
        }
    }

    public function isAccessible(string $url, array $options = []): bool
    {
        try {
            $response = $this->client->head($url, $options);
            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 400;
        } catch (GuzzleException) {
            return false;
        }
    }

    public function getFileInfo(string $url, array $options = []): FileInfo
    {
        try {
            $response = $this->client->head($url, $options);
            $filename = $this->extractFilename($response->getHeaders(), $url);
            $mimeType = $response->getHeaderLine('Content-Type') ?: null;
            $size = (int) $response->getHeaderLine('Content-Length') ?: null;
            $lastModified = $response->getHeaderLine('Last-Modified') ?: null;

            return new FileInfo(
                exists: true,
                size: $size,
                mimeType: $mimeType,
                filename: $filename,
                lastModified: $lastModified
            );
        } catch (GuzzleException $e) {
            return new FileInfo(
                exists: false,
                error: $e->getMessage()
            );
        }
    }

    private function extractFilename(array $headers, string $url): ?string
    {
        // Implementation similar to CurlDownloader
        $contentDisposition = $headers['Content-Disposition'][0] ?? '';

        if (preg_match('/filename="?([^"]+)"?/', $contentDisposition, $matches)) {
            return $matches[1];
        }

        // Fallback to URL basename
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $filename = basename($path);
            if ($filename && $filename !== '/') {
                return $filename;
            }
        }

        return null;
    }
}
