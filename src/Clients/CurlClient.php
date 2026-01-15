<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Clients;

use Vigihdev\Downloader\Contracts\HttpClientInterface;
use Vigihdev\Downloader\Exceptions\DownloadException;

final class CurlClient implements HttpClientInterface
{
    public function get(string $url): string
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            throw new DownloadException("Curl Error: " . $error);
        }

        if ($httpCode >= 400) {
            throw new DownloadException("HTTP Error: " . $httpCode);
        }

        return (string) $response;
    }
}
