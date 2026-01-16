<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

use Vigihdev\Downloader\Results\DownloadResult;

interface DownloadBatchResultInterface
{


    /**
     * Check if all downloads in the batch were successful.
     *
     * @return bool True if all downloads were successful, false otherwise
     */
    public function isAllSuccessful(): bool;

    /**
     * Get the total number of downloads in the batch.
     *
     * @return int The total number of downloads
     */
    public function getTotal(): int;

    /**
     * Get the number of successful downloads in the batch.
     *
     * @return int The number of successful downloads
     */
    public function getSuccess(): int;

    /**
     * Get the number of failed downloads in the batch.
     *
     * @return int The number of failed downloads
     */
    public function getFailed(): int;

    /**
     * Get all download results in the batch.
     *
     * @return DownloadResult[] An array of download results
     */
    public function getResults(): array;
}
