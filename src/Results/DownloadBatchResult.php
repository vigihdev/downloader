<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Results;

use Vigihdev\Downloader\Contracts\DownloadBatchResultInterface;

final class DownloadBatchResult implements DownloadBatchResultInterface
{

    public function __construct(
        private readonly int $total,
        private readonly int $success,
        private readonly int $failed,
        /** @var DownloadResult[] */
        private readonly array $results
    ) {}

    public function isAllSuccessful(): bool
    {
        return $this->total === $this->success;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getSuccess(): int
    {
        return $this->success;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    /**
     *
     * @return DownloadResult[]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
