<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Exceptions;

class DownloadException extends AbstractDownloaderException
{

    public static function alreadyExist(string $path): self
    {
        return new self(
            message: sprintf("File already exist: %s", $path),
            context: ['path' => $path],
            code: 400,
            solutions: [
                "Check if the file already exist and try again.",
                "Verify that the resource exists at the specified path.",
                "Delete the existing file and try again.",
                "Rename the existing file and try again.",
            ],
        );
    }

    public static function emptyFileContent(string $url): self
    {
        return new self(
            message: sprintf("Empty file content for URL: %s", $url),
            context: ['url' => $url],
            code: 400,
            solutions: [
                "Check if the file content is not empty and try again.",
                "Verify that the resource exists at the specified URL.",
            ],
        );
    }

    public static function invalidHttpCode(string $url, int $httpCode): self
    {
        return new self(
            message: sprintf("Invalid HTTP code '%d' for URL: %s", $httpCode, $url),
            context: ['url' => $url, 'httpCode' => $httpCode],
            code: 400,
            solutions: [
                "Check if the HTTP code is valid and try again.",
                "Verify that the resource exists at the specified URL.",
            ],
        );
    }

    public static function invalidContentType(string $url, string $contentType): self
    {
        return new self(
            message: sprintf("Invalid content type '%s' for URL: %s", $contentType, $url),
            context: ['url' => $url, 'contentType' => $contentType],
            code: 400,
            solutions: [
                "Check if the content type is valid and try again.",
                "Verify that the resource exists at the specified URL.",
            ],
        );
    }

    public static function notFoundUrl(string $url): self
    {
        return new self(
            message: sprintf("URL not found: %s", $url),
            context: ['url' => $url],
            code: 404,
            solutions: [
                "Check if the URL is correct and try again.",
                "Verify that the resource exists at the specified URL.",
            ],
        );
    }

    public static function cannotWriteToPath(string $path): self
    {
        return new self(
            message: sprintf("Cannot write to path: %s", $path),
            context: ['path' => $path],
            code: 400,
            solutions: [
                "Check if the path is writable and try again.",
                "Verify that the path exists and is not read-only.",
            ],
        );
    }

    public static function invalidUrl(string $url): self
    {
        return new self(
            message: sprintf("Invalid URL: %s", $url),
            context: ['url' => $url],
            code: 400,
            solutions: [
                "Check if the URL is valid and try again.",
                "Ensure the URL is properly formatted.",
            ],
        );
    }


    public static function unsupportedProtocol(string $url, string $protocol): self
    {
        return new self(
            message: sprintf("Unsupported protocol '%s' for URL: %s", $protocol, $url),
            context: ['url' => $url, 'protocol' => $protocol],
            code: 400,
            solutions: [
                "Check if the protocol is supported and try again.",
                "Ensure the protocol is properly formatted.",
            ],
        );
    }

    public static function fileNotFound(string $url): self
    {
        return new self(
            message: sprintf("File not found at URL: %s", $url),
            context: ['url' => $url],
            code: 404,
            solutions: [
                "Check if the URL is correct and try again.",
                "Verify that the file exists at the specified URL.",
            ],
        );
    }

    public static function downloadFailed(string $url, string $error): self
    {
        return new self(
            message: sprintf("Failed to download from %s: %s", $url, $error),
            context: ['url' => $url, 'error' => $error],
            code: 500,
            solutions: [
                "Check if the server is reachable and try again.",
                "Verify that the server is not overloaded.",
            ],
        );
    }

    public static function invalidMimeType(string $url, ?string $mimeType): self
    {
        $mime = $mimeType ?: 'unknown';
        return new self(
            message: sprintf("Invalid mime type '%s' for URL: %s. Expected image file.", $mime, $url),
            context: ['url' => $url, 'mime' => $mime],
            code: 400,
            solutions: [
                "Check if the file is an image and try again.",
                "Verify that the mime type is correct.",
            ],
        );
    }

    public static function emptyFile(string $path): self
    {
        return new self(
            message: sprintf("Downloaded file is empty: %s", $path),
            context: ['path' => $path],
            code: 400,
            solutions: [
                "Check if the file is not empty and try again.",
                "Verify that the download process was successful.",
            ],
        );
    }

    public static function sizeMismatch(int $expected, int $actual, float $percentage): self
    {
        return new self(
            message: sprintf(
                "File size mismatch: expected %d bytes, got %d bytes (%.1f%% difference)",
                $expected,
                $actual,
                $percentage
            ),
            context: ['expected' => $expected, 'actual' => $actual, 'percentage' => $percentage],
            code: 400,
            solutions: [
                "Check if the file is not corrupted and try again.",
                "Verify that the download process was successful.",
            ],
        );
    }

    public static function cannotMove(string $source, string $destination): self
    {
        return new self(
            message: sprintf("Cannot move file from %s to %s", $source, $destination),
            context: ['source' => $source, 'destination' => $destination],
            code: 400,
            solutions: [
                "Check if the source file exists and try again.",
                "Verify that the destination directory is writable.",
            ],
        );
    }

    public static function cannotBackup(string $original, string $backup): self
    {
        return new self(
            message: sprintf("Cannot create backup of %s to %s", $original, $backup),
            context: ['original' => $original, 'backup' => $backup],
            code: 400,
            solutions: [
                "Check if the original file exists and try again.",
                "Verify that the backup directory is writable.",
            ],
        );
    }
}
