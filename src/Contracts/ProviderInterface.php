<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface ProviderInterface
{
    /**
     * Returns the URL of the image to download.
     *
     * @return string The image URL.
     */
    public function getUrl(): string;

    /**
     * Returns the destination path where the image will be saved.
     *
     * @return string The destination path.
     */
    public function getDestination(): string;
}
