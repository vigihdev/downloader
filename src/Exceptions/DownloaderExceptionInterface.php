<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Exceptions;

use Throwable;

interface DownloaderExceptionInterface extends Throwable
{
    public function getContext(): array;

    public function getSolutions(): array;

    public function getFormattedMessage(): string;
}
