<?php

declare(strict_types=1);

namespace Vigihdev\Downloader;

use Symfony\Component\Filesystem\Filesystem;
use Vigihdev\Downloader\Contracts\{FileDownloaderInterface, TempFileManagerInterface};
use Vigihdev\Downloader\Results\FileInfo;
use Vigihdev\Downloader\Support\{TempFileManager};

abstract class BaseDownloader implements FileDownloaderInterface
{
    protected ?TempFileManagerInterface $tempManager = null;

    protected ?Filesystem $fs = null;

    protected ?FileInfo $fileInfo = null;

    public function __construct()
    {
        if ($this->fs === null) {
            $this->fs = new Filesystem();
        }

        if ($this->tempManager === null) {
            $this->tempManager = new TempFileManager();
        }
    }

    protected function resolveDestination(string $url, string $destination): string
    {
        if (is_dir($destination)) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            $destination = rtrim($destination, '/') . '/' . $filename;
        }
        return $destination;
    }

    protected function isImageMimeType(?string $mimeType): bool
    {
        if ($mimeType === null) {
            return false;
        }
        $imageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp', 'image/tiff', 'image/x-icon',];
        return in_array(strtolower($mimeType), $imageMimes);
    }
}
