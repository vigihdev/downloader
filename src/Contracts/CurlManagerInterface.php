<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface CurlManagerInterface
{
    public function getHandle(): \CurlHandle;
}
