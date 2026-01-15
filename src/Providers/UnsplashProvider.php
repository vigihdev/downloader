<?php

namespace Vigihdev\Downloader\Providers;

use Symfony\Component\Filesystem\Path;

final class UnsplashProvider extends AbstractProvider
{
    public const BASE_URL = 'https://source.unsplash.com';

    private array $ids = [
        '1506744038136-46273834b3fb', // Landscape
        '1470071459604-3b5ec3a7fe05', // Nature
        '1441974231531-c6227db76b6e', // Forest

        // Test
        '1506744038136-46273834b3fb',
        '1506744038136-46273834b3fb',
        '1506744038136-46273834b3fb',
        '1569596082827-c5e8990496cb',
        '1587932775991-708a20af2cc2',
        '1523712999610-f77fbcfc3843',
        '1623166200209-6bd48520d6cb',
        '1532587459811-f057563d1936',

        '1506744038136-46273834b3fb',
        '1569596082827-c5e8990496cb',
        '1587932775991-708a20af2cc2',
        '1523712999610-f77fbcfc3843',
        '1623166200209-6bd48520d6cb',
        '1532587459811-f057563d1936',
        // End Test
        '1569596082827-c5e8990496cb', // City
        '1587932775991-708a20af2cc2', // Beach
        '1523712999610-f77fbcfc3843', // Mountains
        '1623166200209-6bd48520d6cb', // Sunset
        '1532587459811-f057563d1936', // Flowers
    ];

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
        return Path::join($this->destination, 'unsplash-' . $this->randomString() . "-{$this->width}x{$this->height}.jpg");
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function getUrl(): string
    {
        $randomId = $this->ids[array_rand($this->ids)];
        return "https://images.unsplash.com/photo-{$randomId}?w={$this->width}&h={$this->height}&fit=crop";
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
