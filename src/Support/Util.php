<?php

namespace Vigihdev\Downloader\Support;

final class Util
{
    public static function isImageMimeType(?string $mimeType): bool
    {
        if ($mimeType === null) {
            return false;
        }
        $imageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp', 'image/tiff', 'image/x-icon',];
        return in_array(strtolower($mimeType), $imageMimes);
    }

    public static function existsExtension(string $path): bool
    {
        return pathinfo($path, PATHINFO_EXTENSION) !== '';
    }
}
