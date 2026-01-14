<?php

declare(strict_types=1);

namespace Vigihdev\Downloader;

use Vigihdev\Downloader\Validators\{FileValidator, DownloadValidator};
use Vigihdev\Downloader\Exceptions\DownloadException;
use Vigihdev\Downloader\Results\{DownloadResult, FileInfo, MetadataDownload};
use Vigihdev\Downloader\Support\{CurlManager, TempFileManager};

final class CurlDownloader extends BaseDownloader
{
    private const METHOD_NAME = 'curl';

    private ?CurlManager $curlManager = null;

    public function __construct()
    {

        parent::__construct();
        if ($this->tempManager === null) {
            $this->tempManager = new TempFileManager();
        }
    }

    public function download(string $url, string $destination, array $options = []): DownloadResult
    {

        if ($this->curlManager === null) {
            $this->curlManager = new CurlManager($url, $options);
        }

        try {
            $destination = $this->resolveDestination($url, $destination);

            DownloadValidator::validate($url, $destination)
                ->mustBeValidUrl()
                ->mustBeValidDestination();

            $this->fileInfo = $this->curlManager->getFileInfo();
            $exists = $this->fileInfo->isExists();
            $filename = $this->fileInfo->getFilename();
            $mimeType = $this->fileInfo->getMimeType();
            $size = $this->fileInfo->getSize();

            if (!$exists) {
                throw DownloadException::notFoundUrl($url);
            }
            // Check if file is an image
            if (!$this->isImageMimeType($mimeType)) {
                throw DownloadException::invalidMimeType($url, $mimeType);
            }

            // Download file
            $tempFile = $this->downloadToTemp($url);

            FileValidator::validate($tempFile)
                ->mustExist()
                ->mustBeWritable()
                ->mustNotBeEmpty();

            // Move to final destination
            $this->fs->copy($tempFile, $destination);

            // Verify final file
            $finalSize = filesize($destination);
            $finalMime = mime_content_type($destination) ?: $mimeType;

            $this->tempManager->delete($filename);
            return new DownloadResult(
                success: true,
                destination: $destination,
                size: (int)$finalSize,
                mimeType: $finalMime,
                metadata: new MetadataDownload(
                    url: $url,
                    originalSize: (int)$finalSize,
                    downloadTime: date('Y-m-d H:i:s'),
                    method: self::METHOD_NAME,
                ),
            );
        } catch (\Throwable $e) {
            // Cleanup temp file if exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return new DownloadResult(
                success: false,
                destination: $destination,
                size: 0,
                mimeType: '',
                error: $e->getMessage(),
                metadata: new MetadataDownload(
                    url: $url,
                    originalSize: 0,
                    downloadTime: date('Y-m-d H:i:s'),
                    method: self::METHOD_NAME,
                ),
            );
        }
    }

    public function isAccessible(string $url, array $options = []): bool
    {

        if ($this->curlManager === null) {
            $this->curlManager = new CurlManager($url, $options);
        }

        $ch = $this->curlManager->getHandle();
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $result !== false && $httpCode >= 200 && $httpCode < 400;
    }

    public function getFileInfo(string $url, array $options = []): FileInfo
    {
        if ($this->curlManager === null) {
            $this->curlManager = new CurlManager($url, $options);
        }

        if ($this->fileInfo === null) {
            $this->fileInfo = $this->curlManager->getFileInfo();
        }
        return $this->fileInfo;
    }

    /**
     * Download file to temporary location
     * 
     * @param string $url URL to download
     * @return string Path to temporary file
     * @throws DownloadException
     */
    private function downloadToTemp(string $url): string
    {
        $tempFile = $this->tempManager->getPath($this->fileInfo->getFilename());

        if (is_file($tempFile)) {
            unlink($tempFile);
        }

        $ch = $this->curlManager->getHandle();
        $fp = fopen($tempFile, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);

        if (!curl_exec($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            fclose($fp);
            unlink($tempFile);
            throw DownloadException::downloadFailed($url, $error);
        }

        curl_close($ch);
        fclose($fp);

        return $tempFile;
    }
}
