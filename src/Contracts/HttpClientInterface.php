<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

use Vigihdev\Downloader\Exceptions\DownloadException;
use Vigihdev\Downloader\Results\ResponseHeader;

interface HttpClientInterface
{
    /**
     * Sends a GET request to the specified URL.
     *
     * @return string The response body as a string.
     * @throws DownloadException If the request fails.
     */
    public function get(string $url): string;

    /**
     * Sends a HEAD request to the specified URL.
     *
     * @return ResponseHeader The ResponseHeader object containing the response headers.
     * @throws DownloadException If the request fails.
     */
    public function getHeaders(string $url): ResponseHeader;
}
