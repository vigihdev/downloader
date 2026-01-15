<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Providers;

use Symfony\Component\Filesystem\Path;

final class LoremFlickrProvider extends AbstractProvider
{
    private const BASE_URL = 'https://loremflickr.com';

    public function __construct(
        private string $destination,
        private readonly int $width = 640,
        private readonly int $height = 480,
    ) {
        $this->destination = $this->resolveDestination($destination);
    }

    protected function resolveDestination(): string
    {
        return Path::join($this->destination, 'loremflickr-' . $this->randomString() . ".jpg");
    }

    public function getUrl(): string
    {
        return self::BASE_URL . "/{$this->width}/{$this->height}";
    }

    public function getDestination(): string
    {
        return $this->destination;
    }
}
