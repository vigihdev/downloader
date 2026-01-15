<?php

declare(strict_types=1);

namespace Vigihdev\Downloader;

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
        private readonly HttpClientInterface $client
    ) {

        if ($this->fs === null) {
            $this->fs = new Filesystem();
        }
    }

    public function download(): DownloadResult
    {
        try {
            $validator = DownloadValidator::validate($this->provider->getUrl(), $this->provider->getDestination());
            $validator->mustBeValidDestination()->mustNotExistFileDestination();

            $header = $this->client->getHeaders($this->provider->getUrl());
            $validator->mustBeImageMimeType($header->contentType());

            $content = $this->client->get($this->provider->getUrl());
            $this->fs->dumpFile($this->provider->getDestination(), $content);
            $validator->mustExistFileDestination();

            return $this->succesResult();
        } catch (\Throwable | DownloaderExceptionInterface $e) {
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

    private function errorResult(Throwable $e): DownloadResult
    {
        return new DownloadResult(
            success: false,
            destination: $this->provider->getDestination(),
            size: 0,
            mimeType: '',
            metadata: new MetadataDownload(
                url: $this->provider->getUrl(),
                originalSize: 0,
                downloadTime: date('Y-m-d H:i:s'),
                method: get_class($this->provider),
            ),
        );
    }
}
