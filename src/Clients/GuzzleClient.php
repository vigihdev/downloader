<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Vigihdev\Downloader\Contracts\HttpClientInterface;
use Vigihdev\Downloader\Exceptions\DownloadException;
use Vigihdev\Downloader\Results\ResponseHeader;

final class GuzzleClient implements HttpClientInterface
{
    private Client $client;

    public function __construct(array $config = [])
    {
        $defaults = [
            'timeout'         => 30,
            'connect_timeout' => 10,
            'verify'          => true,
        ];

        $this->client = new Client(array_merge($defaults, $config));
    }

    public function get(string $url): string
    {
        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                throw DownloadException::notFoundUrl($url);
            }
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new DownloadException(
                message: $e->getMessage(),
                code: (int)$e->getCode(),
                previous: $e,
                context: ['url' => $url],
                solutions: [
                    "Check if the URL is valid and try again.",
                    "Verify that the resource exists at the specified URL.",
                ],
            );
        }
    }

    public function getHeaders(string $url): ResponseHeader
    {
        try {
            $response = $this->client->request('HEAD', $url);
            return new ResponseHeader(headers: $response->getHeaders());
        } catch (GuzzleException $e) {
            throw new DownloadException(
                message: $e->getMessage(),
                code: (int)$e->getCode(),
                previous: $e,
                context: ['url' => $url],
                solutions: [
                    "Check if the URL is valid and try again.",
                    "Verify that the resource exists at the specified URL.",
                ],
            );
        }
    }
}
