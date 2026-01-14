<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Results;

final class DownloadResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $destination,
        public readonly int $size,
        public readonly string $mimeType,
        public readonly MetadataDownload $metadata,
        public readonly ?string $error = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getMetadata(): MetadataDownload
    {
        return $this->metadata;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }
}
