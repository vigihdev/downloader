<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface ResponseHeaderInterface
{
    public function contentLength(): int;

    public function contentType(): string;
}
