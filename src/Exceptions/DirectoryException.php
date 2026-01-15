<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Exceptions;

class DirectoryException extends AbstractDownloaderException
{


    public static function notFound(string $dirpath): self
    {
        return new self(
            message: sprintf("Direktori not found: %s", $dirpath),
            context: [
                'dirpath' => $dirpath,
            ],
            code: 404,
            solutions: [
                'Check the directory path and make sure the directory exists',
                'Create the directory if it does not exist'
            ]
        );
    }

    public static function notReadable(string $dirpath): self
    {
        return new self(
            message: sprintf("Direktori not readable: %s", $dirpath),
            context: [
                'dirpath' => $dirpath,
            ],
            code: 403,
            solutions: [
                'Check the directory permissions: chmod +r ' . basename($dirpath),
                'Check the directory ownership'
            ]
        );
    }

    public static function notWritable(string $dirpath): self
    {
        return new self(
            message: sprintf("Direktori not writable: %s", $dirpath),
            context: [
                'dirpath' => $dirpath,
            ],
            code: 403,
            solutions: [
                'Check the directory permissions: chmod +w ' . basename($dirpath),
                'Check the directory ownership'
            ]
        );
    }

    public static function alreadyExists(string $dirpath): self
    {
        return new self(
            message: sprintf("Direktori already exists: %s", $dirpath),
            context: [
                'dirpath' => $dirpath,
            ],
            code: 409,
            solutions: [
                'Use a different directory path',
                'Delete the existing directory first',
                'Use the --overwrite flag if available'
            ]
        );
    }

    public static function notEmpty(string $dirpath, int $fileCount = 0): self
    {
        $message = sprintf("Direktori not empty: %s", $dirpath);
        if ($fileCount > 0) {
            $message .= sprintf(" (%d file/folder)", $fileCount);
        }

        return new self(
            message: $message,
            context: [
                'dirpath' => $dirpath,
                'file_count' => $fileCount,
            ],
            code: 409,
            solutions: [
                'Empty the directory first',
                'Use the --force flag to delete the directory and its contents'
            ]
        );
    }

    public static function createFailed(string $dirpath, string $error = ''): self
    {
        $message = sprintf("Failed to create directory: %s", $dirpath);
        if ($error) {
            $message .= ". Error: " . $error;
        }

        return new self(
            message: $message,
            context: [
                'dirpath' => $dirpath,
                'error' => $error,
            ],
            code: 409,
            solutions: [
                'Check the parent directory permissions',
                'Check if the path is valid',
                'Check disk space'
            ]
        );
    }

    public static function deleteFailed(string $dirpath, string $error = ''): self
    {
        $message = sprintf("Failed to delete directory: %s", $dirpath);
        if ($error) {
            $message .= ". Error: " . $error;
        }

        return new self(
            message: $message,
            context: [
                'dirpath' => $dirpath,
                'error' => $error,
            ],
            code: 409,
            solutions: [
                'Check the directory permissions',
                'Make sure the directory is not in use',
                'Empty the directory first'
            ]
        );
    }

    public static function invalidPath(string $dirpath, string $reason = ''): self
    {
        $message = sprintf("Invalid directory path: %s", $dirpath);
        if ($reason) {
            $message .= ". " . $reason;
        }

        return new self(
            message: $message,
            context: [
                'dirpath' => $dirpath,
                'reason' => $reason,
            ],
            code: 400,
            solutions: [
                'Use an absolute or relative path that is valid',
                'Avoid special characters in the path',
                'Check the path length (max 255 characters)'
            ]
        );
    }

    public static function cannotScan(string $dirpath): self
    {
        return new self(
            message: sprintf("Cannot scan directory %s.", basename($dirpath)),
            code: 403,
            context: [
                'path' => $dirpath,
                'basename' => basename($dirpath),
                'dirname' => dirname($dirpath),
            ],
            solutions: [
                "Check if the directory exists and is readable",
                "Check if you have permission to scan the directory",
                "Check for any file system issues",
                "Try again after some time"
            ],
        );
    }

    public static function cannotCreate(string $dirpath): self
    {
        return new self(
            message: sprintf("Cannot create directory %s.", basename($dirpath)),
            code: 403,
            context: [
                'path' => $dirpath,
                'basename' => basename($dirpath),
                'dirname' => dirname($dirpath),
            ],
            solutions: [
                "Check if the parent directory exists and is writable",
                "Check if you have permission to create the directory",
                "Check for any file system issues",
                "Try again after some time"
            ],
        );
    }
}
