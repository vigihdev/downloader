<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface ResponseHeaderInterface
{

    /**
     * Get content length.
     * 
     * @return int
     */
    public function contentLength(): int;

    /**
     * Get content type.
     * 
     * @return string
     */
    public function contentType(): string;
}
