<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

use Vigihdev\Downloader\Results\{DownloadResult, FileInfo};

interface FileDownloaderInterface
{
    /**
     * Download file from URL
     *
     * @param string $url File URL to download
     * @param string $destination Local destination path
     * @param array $options Download options
     * @return DownloadResult
     */
    public function download(string $url, string $destination, array $options = []): DownloadResult;

    /**
     * Check if URL is accessible
     */
    public function isAccessible(string $url, array $options = []): bool;

    /**
     * Get file info without downloading
     */
    public function getFileInfo(string $url, array $options = []): FileInfo;
}
