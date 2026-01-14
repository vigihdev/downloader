<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Results;

use Vigihdev\Downloader\Contracts\MetadataDownloadInterface;

final class MetadataDownload implements MetadataDownloadInterface
{
    public function __construct(
        public readonly string $url,
        public readonly int $originalSize,
        public readonly string $downloadTime,
        public readonly ?string $method = null,
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOriginalSize(): int
    {
        return $this->originalSize;
    }

    public function getDownloadTime(): string
    {
        return $this->downloadTime;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'method' => $this->method,
            'original_size' => $this->originalSize,
            'download_time' => $this->downloadTime,
        ];
    }
}
