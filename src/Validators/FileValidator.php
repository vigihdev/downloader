<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Validators;

use Vigihdev\Downloader\Exceptions\FileException;

final class FileValidator
{
    public function __construct(
        private readonly string $filepath
    ) {}

    public static function validate(string $filepath): static
    {
        return new self($filepath);
    }

    public function mustHaveExtension(): self
    {
        $ext = pathinfo($this->filepath, PATHINFO_EXTENSION);
        if (empty($ext)) {
            throw FileException::notHaveExtension($this->filepath);
        }
        return $this;
    }

    public function mustBeNotExist(): self
    {
        if (file_exists($this->filepath)) {
            throw FileException::exist($this->filepath);
        }
        return $this;
    }

    /**
     * Validate that file exists
     * 
     * @throws FileException
     */
    public function mustExist(): self
    {
        if (!file_exists($this->filepath)) {
            throw FileException::notFound($this->filepath);
        }

        return $this;
    }

    /**
     * Validate that path is a file (not a directory)
     * 
     * @throws FileException
     */
    public function mustBeFile(): self
    {

        if (!is_file($this->filepath)) {
            throw FileException::notFile($this->filepath);
        }

        return $this;
    }

    /**
     * Validate that file is readable
     * 
     * @throws FileException
     */
    public function mustBeReadable(): self
    {
        $this->mustExist();

        if (!is_readable($this->filepath)) {
            throw FileException::notReadable($this->filepath);
        }

        return $this;
    }

    /**
     * Validate that file is writable
     * 
     * @throws FileException
     */
    public function mustBeWritable(): self
    {
        $this->mustExist();

        if (!is_writable($this->filepath)) {
            throw FileException::notWritable($this->filepath);
        }

        return $this;
    }

    /**
     * Validate that file extension matches expected
     * 
     * @throws FileException
     */
    public function mustBeExtension(...$extensions): self
    {
        $ext = strtolower(pathinfo($this->filepath, PATHINFO_EXTENSION));
        if (!in_array($ext, array_map('strtolower', $extensions))) {
            throw FileException::invalidExtension($this->filepath, $ext, implode(', ', $extensions));
        }

        return $this;
    }


    /**
     * Validate that file is not empty
     * 
     * @throws FileException
     */
    public function mustNotBeEmpty(): self
    {
        $this->mustExist();

        if (filesize($this->filepath) === 0) {
            throw FileException::emptyFile($this->filepath);
        }

        return $this;
    }

    /**
     * Validate that file size does not exceed maxSize
     * 
     * @throws FileException
     */
    public function mustNotExceedSize(int $maxSize): self
    {
        $this->mustExist();

        $actualSize = filesize($this->filepath);
        if ($actualSize > $maxSize) {
            throw FileException::fileTooLarge($this->filepath, $maxSize, $actualSize);
        }

        return $this;
    }

    /**
     * Get file size
     */
    public function getSize(): int
    {
        return file_exists($this->filepath) ? filesize($this->filepath) : 0;
    }
}
