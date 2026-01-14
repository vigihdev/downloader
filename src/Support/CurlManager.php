<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Support;

use CurlHandle;
use Vigihdev\Downloader\Contracts\CurlManagerInterface;
use Vigihdev\Downloader\Results\FileInfo;

final class CurlManager implements CurlManagerInterface
{
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_USER_AGENT = 'Mozilla/5.0 (compatible; MockForge/1.0)';
    private const MAX_REDIRECTS = 5;

    private array $defaultOptions = [
        'timeout' => self::DEFAULT_TIMEOUT,
        'connect_timeout' => 10,
        'follow_location' => true,
        'max_redirects' => self::MAX_REDIRECTS,
        'verify_ssl' => true,
        'user_agent' => self::DEFAULT_USER_AGENT,
        'referer' => null,
        'headers' => [],
        'proxy' => null,
        'allow_compression' => true,
    ];

    public static function create(string $url, array $options = []): self
    {
        return new self($url, $options);
    }

    public function __construct(
        private readonly string $url,
        private array $options = []
    ) {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    public function getHandle(): CurlHandle
    {
        $ch = curl_init($this->url);

        // Apply options
        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT => $this->options['timeout'],
            CURLOPT_CONNECTTIMEOUT => $this->options['connect_timeout'],
            CURLOPT_FOLLOWLOCATION => $this->options['follow_location'],
            CURLOPT_MAXREDIRS => $this->options['max_redirects'],
            CURLOPT_USERAGENT => $this->options['user_agent'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FAILONERROR => true,
            CURLOPT_SSL_VERIFYPEER => $this->options['verify_ssl'],
            CURLOPT_SSL_VERIFYHOST => $this->options['verify_ssl'] ? 2 : 0,
            CURLOPT_ENCODING => $this->options['allow_compression'] ? '' : null,
        ]);

        // Custom headers
        if (!empty($this->options['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->options['headers']);
        }

        // Referer
        if (isset($this->options['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $this->options['referer']);
        }

        // Proxy
        if (isset($this->options['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $this->options['proxy']);
        }

        return $ch;
    }

    public function getFileInfo(): FileInfo
    {
        $ch = $this->getHandle();
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = curl_exec($ch);

        if ($headers === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return new FileInfo(exists: false, error: $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        // Clean content type
        if ($contentType && str_contains($contentType, ';')) {
            $contentType = explode(';', $contentType)[0];
        }

        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 400) {
            return new FileInfo(exists: false, error: "HTTP {$httpCode}");
        }

        // Extract filename from headers or URL
        $filename = $this->extractFilename($headers, $this->url);

        return new FileInfo(
            exists: true,
            size: $contentLength > 0 ? (int) $contentLength : null,
            mimeType: $contentType ?: null,
            filename: $filename,
            lastModified: $this->extractLastModified($headers)
        );
    }

    private function isAccessible(): bool
    {
        $ch = $this->getHandle();
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $result !== false && $httpCode >= 200 && $httpCode < 400;
    }

    private function extractFilename(string $headers, string $url): ?string
    {
        // Try Content-Disposition header
        if (preg_match('/filename\s*=\s*["\']?([^"\'\s;]+)["\']?/i', $headers, $matches)) {
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

    private function extractLastModified(string $headers): ?string
    {
        if (preg_match('/Last-Modified:\s*(.+)/i', $headers, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
