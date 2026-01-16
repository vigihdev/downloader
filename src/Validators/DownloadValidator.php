<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Validators;

use Vigihdev\Downloader\Exceptions\{DownloadException, FileException};

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

    public function mustHaveHttpCode200(int $httpCode): self
    {
        if ($httpCode !== 200) {
            throw DownloadException::invalidHttpCode($this->url, $httpCode);
        }
        return $this;
    }

    public function mustBeNotEmptyFileContent(mixed $content): self
    {
        $content = is_string($content) ? $content : '';
        if (trim($content) === '') {
            throw DownloadException::emptyFileContent($this->url);
        }
        return $this;
    }

    public function mustBeExistUrl(int $httpCode)
    {
        if ($httpCode !== 200) {
            throw DownloadException::notFoundUrl($this->url);
        }

        return $this;
    }

    public function mustExistFileDestination(): self
    {
        if (!file_exists($this->destination)) {
            throw FileException::notFound($this->destination);
        }

        return $this;
    }

    public function mustNotExistFileDestination(): self
    {
        if (file_exists($this->destination)) {
            throw DownloadException::alreadyExist($this->destination);
        }

        return $this;
    }

    public function mustNotExceedSize(int $size, int $maxSize, string $unit = 'bytes'): self
    {
        if ($size > $maxSize) {
            throw FileException::tooLarge($this->destination, $maxSize, $size, $unit);
        }
        return $this;
    }

    public function mustBeImageMimeType(?string $contentType): self
    {
        if (!is_string($contentType) || strpos($contentType, 'image/') !== 0) {
            throw DownloadException::invalidMimeType($this->url, $contentType);
        }
        return $this;
    }
}
