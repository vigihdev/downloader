# Downloader

![Tests](https://github.com/vigihdev/downloader/actions/workflows/tests.yml/badge.svg?branch=main)
![Push](https://github.com/vigihdev/downloader/actions/workflows/push.yml/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)

A PHP library for downloading files from the command line with support for multiple image providers.

## Features

- Download files from any URL
- Support for multiple image providers (Picsum, Unsplash, LoremFlickr)
- Configurable destination directory
- Option to overwrite existing files
- Error handling with solution suggestions

## Installation

Install the package via Composer:

```bash
composer require vigihdev/downloader
```

## Usage

```php
use Vigihdev\Downloader\Clients\GuzzleClient;
use Vigihdev\Downloader\Exceptions\DownloaderExceptionInterface;
use Vigihdev\Downloader\ImageDownloader;
use Vigihdev\Downloader\Providers\{LoremFlickrProvider, PicsumProvider, UnsplashProvider, NativeUrlProvider};

// Initialize the HTTP client
$client = new GuzzleClient();

// Download from Picsum
$picsumDownload = new ImageDownloader(
    provider: new PicsumProvider('/path/to/download/directory'),
    client: $client,
);

// Download from Unsplash
$unsplashDownload = new ImageDownloader(
    provider: new UnsplashProvider('/path/to/download/directory'),
    client: $client,
);

// Download from LoremFlickr
$loremFlickr = new ImageDownloader(
    provider: new LoremFlickrProvider('/path/to/download/directory'),
    client: $client,
);

// Handle download attempts with error handling
try {
    $result = $picsumDownload->download();
    var_dump($result);
} catch (DownloaderExceptionInterface $e) {
    echo $e->getMessage() . "\n";
    var_dump($e->getSolutions());
}

// Download from a direct URL
$nativeUrl = new NativeUrlProvider(
    destination: '/path/to/download/directory',
    url: 'https://example.com/image.jpg',
    allowOverwrite: true,
);

$imageDownload = new ImageDownloader(
    provider: $nativeUrl,
    client: $client,
);

try {
    $result = $imageDownload->download();
    var_dump($result);
} catch (DownloaderExceptionInterface $e) {
    var_dump($e->getMessage());
    var_dump($e->getSolutions());
}
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
