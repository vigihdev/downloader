<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Providers;

use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Downloader\Contracts\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{

    abstract protected function resolveDestination(): string;
    abstract public function getUrl(): string;
    abstract public function getDestination(): string;

    /**
     * Get random string.
     * 
     * @param int $length
     * @return string
     */
    protected function randomString(int $length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }

    /**
     * Get SplFileInfo.
     * 
     * @param string $path
     * @return SplFileInfo
     */
    protected function splFileInfo(string $path): SplFileInfo
    {
        return new SplFileInfo($path);
    }

    /**
     * Transform destination.
     * 
     * @param string $url URL to download
     * @param string $destination Destination path
     * @param string $prefix Prefix to add to filename
     * @return string Transformed destination path
     */
    protected function transformDestination(string $url, string $destination, string $prefix = ''): string
    {
        if (pathinfo($destination, PATHINFO_EXTENSION) === '') {
            $path = parse_url($url, PHP_URL_PATH);
            $ext = pathinfo((string) $path, PATHINFO_EXTENSION);
            $name = pathinfo((string) $path, PATHINFO_FILENAME);
            $name = preg_replace('/[^\w_\-]+/', '', $name);

            if ($ext === '') {
                return Path::join($destination, "{$prefix}{$name}.jpg");
            }

            return Path::join($destination, "{$prefix}{$name}.{$ext}");
        }

        return $destination;
    }
}
