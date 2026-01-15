<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Support;


final class DownloadHelper
{

    private static function cleanFilename(string $filename): string
    {
        return preg_replace('/[^\w\\.-]+/', '', $filename);
    }
}
