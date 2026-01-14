<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Results;

final class FileInfo
{
    public function __construct(
        public readonly bool $exists,
        public readonly ?int $size = null,
        public readonly ?string $mimeType = null,
        public readonly ?string $filename = null,
        public readonly ?string $lastModified = null,
        public readonly ?string $error = null
    ) {}

    public function isExists(): bool
    {
        return $this->exists;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getLastModified(): ?string
    {
        return $this->lastModified;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
