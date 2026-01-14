<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Validators;

use Vigihdev\Downloader\Exceptions\DownloadException;

final class DownloadValidator
{
    public function __construct(
        private readonly string $url,
        private readonly string $destination
    ) {}

    public static function validate(string $url, string $destination): self
    {
        return new self($url, $destination);
    }

    public function mustBeValidUrl(): self
    {

        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw DownloadException::invalidUrl($this->url);
        }

        $scheme = parse_url($this->url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'])) {
            throw DownloadException::unsupportedProtocol($this->url, $scheme);
        }

        return $this;
    }

    public function mustBeValidDestination(): self
    {

        $directory = $this->destination;
        if (pathinfo($directory, PATHINFO_EXTENSION) !== '') {
            $directory = dirname($directory);
        }

        DirectoryValidator::validate($directory)
            ->mustExist()
            ->mustBeReadable()
            ->mustBeWritable();

        return $this;
    }
}
