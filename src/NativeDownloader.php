<?php

declare(strict_types=1);

namespace Vigihdev\Downloader;

use Vigihdev\Downloader\Validators\{DownloadValidator, FileValidator};
use Vigihdev\Downloader\Exceptions\DownloadException;
use Vigihdev\Downloader\Results\{DownloadResult, FileInfo, MetadataDownload};

final class NativeDownloader extends BaseDownloader
{
    private array $defaultOptions = [
        'timeout' => 30,
        'context' => null,
        'max_redirects' => 5,
        'user_agent' => 'Mozilla/5.0 (compatible; VigihdevDownloader/1.0)',
        'verify_ssl' => true,
    ];

    public function __construct(
        private array $options = []
    ) {
        parent::__construct();
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Download file from URL to destination
     * 
     * @param string $url URL to download
     * @param string $destination Destination path
     * @param array $options Additional options for downloader
     * @return DownloadResult Download result object
     */
    public function download(string $url, string $destination, array $options = []): DownloadResult
    {
        $options = array_merge($this->options, $options);

        try {

            $destination = $this->resolveDestination($url, $destination);

            // Validate URL Destination
            DownloadValidator::validate($url, $destination)
                ->mustBeValidUrl()
                ->mustBeValidDestination();

            // Get file info first
            $fileInfo = $this->getFileInfo($url, $options);
            $exists = $this->fileInfo->isExists();
            $filename = $this->fileInfo->getFilename();
            $mimeType = $this->fileInfo->getMimeType();
            $size = $this->fileInfo->getSize();

            if (!$exists) {
                throw DownloadException::fileNotFound($url);
            }

            // Check if it's an image
            if (!$this->isImageMimeType($mimeType)) {
                throw DownloadException::invalidMimeType($url, $mimeType);
            }

            // Create stream context
            $context = $this->createStreamContext($options);

            // Download to temp file first
            $tempFile = $this->downloadToTemp($url, $context);

            FileValidator::validate($tempFile)
                ->mustExist()
                ->mustBeWritable()
                ->mustNotBeEmpty();

            // Move to final destination
            $this->fs->copy($tempFile, $destination);

            // Get final file info
            $finalSize = filesize($destination);
            $finalMime = mime_content_type($destination) ?: $fileInfo->mimeType;

            $this->tempManager->delete($filename);
            return new DownloadResult(
                success: true,
                destination: $destination,
                size: $finalSize,
                mimeType: $finalMime,
                metadata: new MetadataDownload(
                    url: $url,
                    method: 'native',
                    originalSize: $fileInfo->size,
                    downloadTime: date('Y-m-d H:i:s'),
                )
            );
        } catch (\Throwable $e) {
            // Cleanup temp file if exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return new DownloadResult(
                success: false,
                destination: $destination,
                size: 0,
                mimeType: '',
                error: $e->getMessage(),
                metadata: new MetadataDownload(
                    url: $url,
                    method: 'native',
                    originalSize: 0,
                    downloadTime: date('Y-m-d H:i:s'),
                )
            );
        }
    }

    /**
     * Check if URL is accessible
     * 
     * @param string $url URL to check
     * @param array $options Additional options for downloader
     * @return bool True if URL is accessible, false otherwise
     */
    public function isAccessible(string $url, array $options = []): bool
    {
        $options = array_merge($this->options, $options);

        // Use headers only request
        $context = $this->createStreamContext($options, true);

        try {
            $headers = get_headers($url, true, $context);

            if ($headers === false) {
                return false;
            }

            // Extract status code from first header
            $statusLine = $headers[0] ?? '';
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches);

            $statusCode = $matches[1] ?? 0;
            return $statusCode >= 200 && $statusCode < 400;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get file information from URL
     * 
     * @param string $url URL to check
     * @param array $options Additional options for downloader
     * @return FileInfo File information object
     */
    public function getFileInfo(string $url, array $options = []): FileInfo
    {
        $options = array_merge($this->options, $options);

        try {
            $context = $this->createStreamContext($options, true);
            $headers = get_headers($url, true, $context);

            if ($headers === false) {
                return $this->fileInfo = new FileInfo(exists: false, error: 'Could not retrieve headers');
            }

            // Extract status code
            $statusLine = $headers[0] ?? '';
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches);
            $statusCode = (int) ($matches[1] ?? 0);

            if ($statusCode < 200 || $statusCode >= 400) {
                return $this->fileInfo = new FileInfo(exists: false, error: "HTTP {$statusCode}");
            }

            // Extract file info from headers
            $contentLength = $this->extractContentLength($headers);
            $contentType = $this->extractContentType($headers);
            $lastModified = $this->extractLastModified($headers);
            $filename = $this->extractFilename($headers, $url);

            return $this->fileInfo = new FileInfo(
                exists: true,
                size: $contentLength,
                mimeType: $contentType,
                filename: $filename,
                lastModified: $lastModified
            );
        } catch (\Throwable $e) {
            return $this->fileInfo = new FileInfo(exists: false, error: $e->getMessage());
        }
    }

    /**
     * Create a stream context for HTTP requests.
     *
     * @param array $options Array of download options
     * @param bool $headersOnly Whether to request headers only
     * @return resource Stream context resource
     */
    private function createStreamContext(array $options, bool $headersOnly = false)
    {
        $contextOptions = [
            'http' => [
                'timeout' => $options['timeout'],
                'user_agent' => $options['user_agent'],
                'follow_location' => 1,
                'max_redirects' => $options['max_redirects'],
                'ignore_errors' => true, // Don't fail on 404/500
            ],
            'ssl' => [
                'verify_peer' => $options['verify_ssl'],
                'verify_peer_name' => $options['verify_ssl'],
                'allow_self_signed' => !$options['verify_ssl'],
            ]
        ];

        if ($headersOnly) {
            $contextOptions['http']['method'] = 'HEAD';
        }

        // Merge custom context if provided
        if (isset($options['context']) && is_array($options['context'])) {
            $contextOptions = array_merge_recursive($contextOptions, $options['context']);
        }

        return stream_context_create($contextOptions);
    }

    /**
     * Download file to temporary location
     * 
     * @param string $url URL to download
     * @param resource $context Stream context resource
     * @return string Path to temporary file
     */
    private function downloadToTemp(string $url, $context): string
    {
        $tempFile = $this->tempManager->getPath($this->fileInfo->getFilename());

        // Use file_get_contents with context
        $content = file_get_contents($url, false, $context);

        if ($content === false) {
            $error = error_get_last();
            throw DownloadException::downloadFailed($url, $error['message'] ?? 'Unknown error');
        }

        if (file_put_contents($tempFile, $content) === false) {
            throw DownloadException::cannotWriteToPath($tempFile);
        }

        return $tempFile;
    }

    /**
     * Extract Content-Length header value
     * 
     * @param array $headers Array of HTTP headers
     * @return ?int Content-Length value or null if not found
     */
    private function extractContentLength(array $headers): ?int
    {

        // Handle both array and string formats
        $contentLength = array_filter($headers, fn($key) => is_string($key) && strtolower($key) === 'content-length', ARRAY_FILTER_USE_KEY);
        if (is_array($contentLength)) {
            $contentLength = end($contentLength);
        }

        return $contentLength ? (int) $contentLength : null;
    }

    /**
     * Extract Content-Type header value
     * 
     * @param array $headers Array of HTTP headers
     * @return ?string Content-Type value or null if not found
     */
    private function extractContentType(array $headers): ?string
    {

        $contentType = array_filter($headers, fn($key) => is_string($key) && strtolower($key) === 'content-type', ARRAY_FILTER_USE_KEY);

        if (is_array($contentType)) {
            $contentType = end($contentType) ?? null;
        }

        if ($contentType && str_contains($contentType, ';')) {
            $contentType = explode(';', $contentType)[0];
        }

        return $contentType ? trim($contentType) : null;
    }

    /**
     * Extract Last-Modified header value
     * 
     * @param array $headers Array of HTTP headers
     * @return ?string Last-Modified value or null if not found
     */
    private function extractLastModified(array $headers): ?string
    {
        $lastModified = array_filter($headers, fn($key) => is_string($key) && strtolower($key) === 'last-modified', ARRAY_FILTER_USE_KEY);

        if (is_array($lastModified)) {
            $lastModified = $lastModified[0] ?? null;
        }

        return $lastModified ? trim($lastModified) : null;
    }

    /**
     * Extract filename from Content-Disposition header or URL
     * 
     * @param array $headers Array of HTTP headers
     * @param string $url URL to extract filename from
     * @return ?string Filename or null if not found
     */
    private function extractFilename(array $headers, string $url): ?string
    {
        // Try Content-Disposition header
        $contentDisposition = array_filter($headers, fn($key) => is_string($key) && strtolower($key) === 'content-disposition', ARRAY_FILTER_USE_KEY);

        if (is_array($contentDisposition)) {
            $contentDisposition = $contentDisposition[0] ?? '';
        }

        if (preg_match('/filename\s*=\s*["\']?([^"\'\s;]+)["\']?/i', $contentDisposition, $matches)) {
            return urldecode($matches[1]);
        }

        // Extract from URL
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $filename = basename($path);
            if ($filename && $filename !== '/') {
                return urldecode($filename);
            }
        }

        return null;
    }
}
