<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Providers;

use Symfony\Component\Filesystem\Path;

final class PicsumProvider extends AbstractProvider
{
    private const BASE_URL = 'https://picsum.photos';

    public function __construct(
        private string $destination,
        private readonly int $width = 640,
        private readonly int $height = 480,
        private readonly bool $allowOverwrite = false,
        private readonly int $maxFileSize = 1024 * 1024 * 4, // 4 MB
    ) {
        $this->destination = $this->resolveDestination($destination);
    }

    protected function resolveDestination(): string
    {
        return Path::join($this->destination, 'picsum-' . $this->randomString() . "-{$this->width}x{$this->height}.jpg");
    }

    public function getUrl(): string
    {
        return self::BASE_URL . "/{$this->width}/{$this->height}";
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function allowOverwrite(): bool
    {
        return $this->allowOverwrite;
    }

    public function maxFileSize(): int
    {
        return $this->maxFileSize;
    }
}
