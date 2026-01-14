<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Validators;

use Vigihdev\Downloader\Exceptions\DirectoryException;

final class DirectoryValidator
{

    private function __construct(
        private readonly string $dirpath,
    ) {}

    /**
     * Memvalidasi apakah direktori ada.
     * 
     * @param string $dirpath Path ke direktori
     * @return self
     */
    public static function validate(string $dirpath): self
    {
        return new self($dirpath);
    }

    /**
     * Memastikan direktori ada.
     *
     * @return self
     * @throws DirectoryException
     */
    public function mustExist(): self
    {
        if (!is_dir($this->dirpath)) {
            throw DirectoryException::notFound($this->dirpath);
        }
        return $this;
    }

    /**
     * Memastikan direktori dapat dibaca.
     *
     * @return self
     * @throws DirectoryException
     */
    public function mustBeReadable(): self
    {
        $this->mustExist();

        if (!is_readable($this->dirpath)) {
            throw DirectoryException::notReadable($this->dirpath);
        }
        return $this;
    }

    /**
     * Memastikan direktori dapat ditulis.
     *
     * @return self
     * @throws DirectoryException
     */
    public function mustBeWritable(): self
    {
        $this->mustExist();

        if (!is_writable($this->dirpath)) {
            throw DirectoryException::notWritable($this->dirpath);
        }
        return $this;
    }

    /**
     * Memastikan direktori kosong.
     *
     * @return self
     * @throws DirectoryException
     */
    public function mustBeEmpty(): self
    {
        $this->mustExist();

        if (!($handle = opendir($this->dirpath))) {
            throw DirectoryException::cannotScan($this->dirpath);
        }

        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                throw DirectoryException::notEmpty($this->dirpath);
            }
        }

        closedir($handle);
        return $this;
    }

    /**
     * Memastikan direktori dapat dibuat jika belum ada.
     *
     * @return self
     * @throws DirectoryException
     */
    public function ensureExists(): self
    {
        if (!is_dir($this->dirpath)) {
            $parentDir = dirname($this->dirpath);

            if (!is_dir($parentDir) && !mkdir($parentDir, 0755, true)) {
                throw DirectoryException::cannotCreate($parentDir);
            }

            if (!mkdir($this->dirpath, 0755, true)) {
                throw DirectoryException::cannotCreate($this->dirpath);
            }
        }

        return $this;
    }

    /**
     * Memastikan direktori dapat dihapus.
     *
     * @param bool $recursive Hapus direktori beserta isinya
     * @return self
     * @throws DirectoryException
     */
    public function ensureDeletable(bool $recursive = false): self
    {
        $this->mustExist();

        if (!$recursive && $this->isNotEmpty()) {
            throw DirectoryException::notEmpty($this->dirpath);
        }

        return $this;
    }

    /**
     * Periksa apakah direktori tidak kosong.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        $this->mustExist();

        if (!($handle = opendir($this->dirpath))) {
            throw DirectoryException::cannotScan($this->dirpath);
        }

        $hasContent = false;
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $hasContent = true;
                break;
            }
        }

        closedir($handle);
        return $hasContent;
    }
}
