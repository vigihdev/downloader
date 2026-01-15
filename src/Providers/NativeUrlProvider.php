<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Providers;


final class NativeUrlProvider extends AbstractProvider
{

    public function __construct(
        private string $destination,
        private readonly string $url,
        private readonly bool $allowOverwrite = false,
        private readonly int $maxFileSize = 1024 * 1024 * 4, // 4 MB
    ) {
        $this->destination = $this->resolveDestination($destination);
    }

    protected function resolveDestination(): string
    {
        return $this->transformDestination($this->url, $this->destination, 'native-');
    }

    public function getUrl(): string
    {
        return $this->url;
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
