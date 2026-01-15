<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

use Vigihdev\Downloader\Results\DownloadResult;

interface ImageDownloaderInterface
{

    /**
     * Downloads an image from a provider.
     *
     * @return DownloadResult The download result.
     * @throws DownloaderExceptionInterface If the download fails.
     */
    public function download(): DownloadResult;
}
