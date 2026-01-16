<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface ArrayAbleInterface
{
    /**
     * Get array representation.
     * 
     * @return array
     */
    public function toArray(): array;
}
