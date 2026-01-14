<?php

declare(strict_types=1);

namespace Vigihdev\Downloader;

use GuzzleHttp\Client;
use Vigihdev\Downloader\Contracts\FileDownloaderInterface;
use Vigihdev\Downloader\Results\{DownloadBatchResult, DownloadResult};

final class DownloadManager
{
    private FileDownloaderInterface $downloader;

    private array $defaultOptions = [];

    /**
     * Download multiple images from URLs to directory
     * 
     * @param array $urls Array of image URLs to download
     * @param string $directory Directory to save downloaded images
     * @param array $options Additional options for downloader
     * @return DownloadBatchResult Batch download result object
     */
    public static function downloadImages(array $urls, string $directory, array $options = []): DownloadBatchResult
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($urls as $url) {
            // Download
            $self = new self(null, $options);
            $result = $self->downloadImage($url, $directory, $options);
            $results[] = $result;

            if ($result->isSuccess()) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        return new DownloadBatchResult(
            total: count($urls),
            success: $successCount,
            failed: $failureCount,
            results: $results,
        );
    }

    /**
     * Download image from URL to destination
     * 
     * @param string $url URL to download
     * @param string $destination Destination path
     * @param array $options Additional options for downloader
     * @return DownloadResult Download result object
     */
    public static function downloadImage(string $url, string $destination, array $options = []): DownloadResult
    {
        $self = new self();
        $options = array_merge($self->defaultOptions, $options);
        $options['validate_mime_type'] = true;
        return $self->downloader->download($url, $destination, $options);
    }

    public function __construct(
        ?FileDownloaderInterface $downloader = null,
        array $options = []
    ) {

        $this->downloader = $downloader ?? $this->createDefaultDownloader();
        $this->defaultOptions = $options;
    }

    /**
     * Validate if URL points to an image
     * 
     * @param string $url URL to validate
     * @param array $options Additional options for downloader
     * @return bool True if URL points to an image, false otherwise
     */
    public function validateImageUrl(string $url, array $options = []): bool
    {
        $fileInfo = $this->downloader->getFileInfo($url, $options);

        if (!$fileInfo->isExists()) {
            return false;
        }
        // Check if it's an image
        return $fileInfo->getMimeType() && str_starts_with($fileInfo->getMimeType(), 'image/');
    }

    /**
     * Create default downloader based on available extensions
     * 
     * @return FileDownloaderInterface Default downloader instance
     */
    private function createDefaultDownloader(): FileDownloaderInterface
    {
        // Prefer curl, fallback to guzzle, then file_get_contents
        if (extension_loaded('curl')) {
            return new CurlDownloader();
        }

        if (class_exists(Client::class)) {
            return new GuzzleDownloader();
        }

        // Fallback to basic implementation
        return new NativeDownloader();
    }
}
