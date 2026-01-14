<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Exceptions;

final class FileException extends BaseDownloaderException
{

    public static function exist(string $filepath): self
    {
        return new self(
            message: sprintf("File %s already exists", $filepath),
            context: [
                'filepath' => $filepath,
            ],
            code: 400,
            solutions: [
                'Check filepath and ensure file not exist',
                'Override file is not recommended',
            ]
        );
    }

    public static function notHaveExtension(string $filepath): self
    {
        return new self(
            message: sprintf("File '%s' is not a valid file", $filepath),
            context: [
                'filepath' => $filepath,
            ],
            code: 400,
            solutions: [
                'Check file extension from filepath',
            ]
        );
    }

    public static function notFile(string $filepath): self
    {
        return new self(
            message: sprintf("File '%s' is not a valid file", $filepath),
            context: [
                'filepath' => $filepath,
            ],
            code: 400,
            solutions: [
                'Check file extension from filepath',
            ]
        );
    }

    public static function notFound(string $filepath): self
    {
        return new self(
            message: sprintf("File not found: %s", basename($filepath)),
            context: [
                'filepath' => $filepath,
            ],
            code: 404,
            solutions: [
                'Check filepath and ensure file exists',
                'Check file permission: chmod +r ' . basename($filepath)
            ]
        );
    }

    public static function notReadable(string $filepath): self
    {
        return new self(
            message: "File not readable",
            context: [
                'filepath' => $filepath,
            ],
            code: 403,
            solutions: [
                'Check file permission: chmod +r ' . basename($filepath)
            ]
        );
    }

    public static function notWritable(string $filepath): self
    {
        return new self(
            message: "File not writable",
            context: [
                'filepath' => $filepath,
            ],
            code: 403,
            solutions: [
                'Check file permission: chmod +w ' . basename($filepath),
                'Check parent directory permission'
            ]
        );
    }

    public static function invalidExtension(string $filepath, string $expected, string $allowed): self
    {
        $actual = pathinfo($filepath, PATHINFO_EXTENSION) ?: 'none';

        return new self(
            message: sprintf("File extension .%s not allow. (allowed: %s)", $actual, $allowed),
            context: [
                'filepath' => $filepath,
                'expected_extension' => $expected,
                'actual_extension' => $actual,
            ],
            code: 400,
            solutions: [
                sprintf("Current extension: .%s", $actual),
                'Change file extension to .' . $expected
            ]
        );
    }

    public static function invalidJson(string $filepath, ?string $error = null): self
    {
        $message = "JSON format is invalid";
        if ($error) {
            $message .= ": " . $error;
        }

        return new self(
            message: $message,
            context: [
                'filepath' => $filepath,
                'error' => $error,
            ],
            code: 400,
            solutions: [
                'Validate file using jsonlint.com',
                'Check JSON syntax (comma, bracket, quotes)'
            ]
        );
    }

    public static function invalidXml(string $filepath, ?string $error = null): self
    {
        $message = "XML format is invalid";
        if ($error) {
            $message .= ": " . $error;
        }

        return new self(
            message: $message,
            context: [
                'filepath' => $filepath,
                'error' => $error,
            ],
            code: 400,
            solutions: [
                'Validate XML file using XML validator',
                'Check XML tag opening and closing'
            ]
        );
    }

    public static function invalidCsv(string $filepath, ?string $error = null): self
    {
        $message = "CSV format is invalid";
        if ($error) {
            $message .= ": " . $error;
        }

        return new self(
            message: $message,
            context: [
                'filepath' => $filepath,
                'error' => $error,
            ],
            code: 400,
            solutions: [
                'Check CSV delimiter and format',
                'Ensure consistent column count'
            ]
        );
    }

    public static function fileTooLarge(string $filepath, int $maxSize, int $actualSize): self
    {
        return new self(
            message: sprintf(
                'File size exceeds maximum allowed: %s (max %s)',
                $actualSize,
                $maxSize
            ),
            context: [
                'filepath' => $filepath,
                'max_size' => $maxSize,
                'actual_size' => $actualSize,
            ],
            code: 400,
            solutions: [
                'Compress file or use a smaller file',
                'Increase max file size if allowed'
            ]
        );
    }

    public static function emptyFile(string $filepath): self
    {
        return new self(
            message: 'File is empty or does not contain content',
            context: [
                'filepath' => $filepath,
            ],
            code: 400,
            solutions: [
                'Ensure file has valid content',
                'Check file creation process'
            ]
        );
    }
}
