<?php

declare(strict_types=1);

namespace VigihDev\Downloader\Enums;

enum Provider: string
{
    case LOREM_FLICKR = 'LoremFlickr';
    case PICSUM = 'Picsum';
    case NATIVE_URL = 'NativeUrl';
    case UNSPLASH = 'Unsplash';
}
