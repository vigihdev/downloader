<?php

declare(strict_types=1);

namespace Vigihdev\Downloader;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use SplFileInfo;
use Throwable;
use Symfony\Component\Filesystem\Filesystem;
use Vigihdev\Downloader\Contracts\{HttpClientInterface, ProviderInterface, ImageDownloaderInterface};
use Vigihdev\Downloader\Exceptions\{DownloaderExceptionInterface, DownloadException};
use Vigihdev\Downloader\Results\{DownloadResult, MetadataDownload};
use Vigihdev\Downloader\Validators\DownloadValidator;

final class ImageDownloader implements ImageDownloaderInterface
{

    private ?Filesystem $fs = null;
    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly HttpClientInterface $client,
        private readonly bool $useHeaderHint = false,
    ) {

        if ($this->fs === null) {
            $this->fs = new Filesystem();
        }
    }

    public function download(): DownloadResult
    {
        try {
            $validator = DownloadValidator::validate($this->provider->getUrl(), $this->provider->getDestination());
            $validator->mustBeValidDestination();

            if (!$this->provider->allowOverwrite()) {
                $validator->mustNotExistFileDestination();
            }

            if ($this->useHeaderHint) {
                $header = $this->client->getHeaders($this->provider->getUrl());
                $validator->mustBeImageMimeType($header->contentType())
                    ->mustNotExceedSize($header->contentLength(), $this->provider->maxFileSize());
            }

            $content = $this->client->get($this->provider->getUrl());
            $this->fs->dumpFile($this->provider->getDestination(), $content);
            $validator->mustExistFileDestination();

            if (!$this->useHeaderHint) {
                $validator->mustNotExceedSize(strlen($content), $this->provider->maxFileSize())
                    ->mustBeImageMimeType(mime_content_type($this->provider->getDestination()));
            }

            return $this->succesResult();
        } catch (\Throwable | DownloaderExceptionInterface $e) {

            if (is_file($this->provider->getDestination())) {
                unlink($this->provider->getDestination());
            }

            $context = method_exists($e, 'getContext') ? $e->getContext() : ['url' => $this->provider->getUrl()];
            $solutions = method_exists($e, 'getSolutions') ? $e->getSolutions() : [];
            throw new DownloadException(
                message: $e->getMessage(),
                code: (int)$e->getCode(),
                previous: $e->getPrevious() ?? null,
                context: $context,
                solutions: $solutions,
            );
        }
    }

    private static function splFileInfo(string $path): SplFileInfo
    {
        return new SplFileInfo($path);
    }

    private function succesResult(): DownloadResult
    {
        $info = self::splFileInfo($this->provider->getDestination());
        return new DownloadResult(
            success: true,
            destination: $this->provider->getDestination(),
            size: (int)$info->getSize(),
            mimeType: mime_content_type($this->provider->getDestination()),
            metadata: new MetadataDownload(
                url: $this->provider->getUrl(),
                originalSize: (int)$info->getSize(),
                downloadTime: date('Y-m-d H:i:s'),
                method: get_class($this->provider),
            ),
        );
    }

    private function dateNow(): string
    {
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        return $date->format('Y-m-d H:i:s');
    }
}
