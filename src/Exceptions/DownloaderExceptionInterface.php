<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Exceptions;

use Throwable;

interface DownloaderExceptionInterface extends Throwable
{
    /**
     * Get context.
     * 
     * @return array
     */
    public function getContext(): array;

    /**
     * Get solutions.
     * 
     * @return array
     */
    public function getSolutions(): array;

    /**
     * Get formatted message.
     * 
     * @return string
     */
    public function getFormattedMessage(): string;
}
