<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface MetadataDownloadInterface extends ArrayAbleInterface
{

    public function getUrl(): string;

    public function getOriginalSize(): int;

    public function getDownloadTime(): string;

    public function getMethod(): ?string;
}
