<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Contracts;

interface TempFileManagerInterface
{
    /**
     * Create a temporary file with the given content.
     *
     * @param string $filename Name of the file
     * @param string $content Content of the file
     * @return string Path to the temporary file
     */
    public function put(string $filename, string $content): string;

    /**
     * Check if a temporary file exists.
     *
     * @param string $filename Name of the file
     * @return bool
     */
    public function exists(string $filename): bool;

    /**
     * Read the content of a temporary file.
     *
     * @param string $filename Name of the file
     * @return string|null Content of the file or null if not found
     */
    public function get(string $filename): ?string;

    /**
     * Delete a temporary file.
     *
     * @param string $filename Name of the file
     * @return bool Whether the deletion was successful
     */
    public function delete(string $filename): bool;

    /**
     * Get the path to a temporary file.
     *
     * @param string $filename Name of the file
     * @return string Path to the temporary file
     */
    public function getPath(string $filename): string;

    /**
     * Delete all temporary files.
     *
     * @return void
     */
    public function clearAll(): void;
}
