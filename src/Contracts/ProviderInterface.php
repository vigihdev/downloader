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

    /**
     * Returns whether the downloader should overwrite the file if it already exists.
     *
     * @return bool True if the file should be overwritten, false otherwise.
     */
    public function allowOverwrite(): bool;

    /**
     * Returns whether the downloader should max file size.
     *
     * @return int The max file size in bytes.
     */
    public function maxFileSize(): int;
}
