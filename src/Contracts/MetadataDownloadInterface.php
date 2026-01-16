<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface MetadataDownloadInterface extends ArrayAbleInterface
{

    /**
     * Get URL.
     * 
     * @return string
     */
    public function getUrl(): string;

    /**
     * Get original size.
     * 
     * @return int
     */
    public function getOriginalSize(): int;

    /**
     * Get download time.
     * 
     * @return string
     */
    public function getDownloadTime(): string;

    /**
     * Get method.
     * 
     * @return string|null
     */
    public function getMethod(): ?string;
}
