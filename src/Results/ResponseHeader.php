<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Results;

use Vigihdev\Downloader\Contracts\ResponseHeaderInterface;

final class ResponseHeader implements ResponseHeaderInterface
{
    public function __construct(
        private readonly array $headers,
    ) {}

    public function contentLength(): int
    {
        $length = current($this->filter('content-length')) ?? 0;
        return (int) $length;
    }

    public function contentType(): string
    {
        $type = current($this->filter('content-type')) ?? '';
        return $type;
    }

    private function filter(string $name): array
    {
        $result = array_filter($this->headers, function ($key) use ($name) {
            return strtolower($key) === strtolower($name);
        }, ARRAY_FILTER_USE_KEY);

        return array_values(current($result) ?? []);
    }
}
