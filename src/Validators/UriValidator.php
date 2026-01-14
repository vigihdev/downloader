<?php

declare(strict_types=1);

namespace Vigihdev\Downloader\Validators;

use Vigihdev\Downloader\Exceptions\UriException;

final class UriValidator
{
    /**
     * Skema URI yang umum didukung.
     */
    private const COMMON_SCHEMES = ['http', 'https', 'ftp', 'file', 'data'];

    private function __construct(
        private readonly string $uri,
    ) {}

    /**
     * Memvalidasi URI.
     *
     * @param string $uri URI untuk divalidasi
     * @return self
     */
    public static function validate(string $uri): self
    {
        return new self($uri);
    }

    /**
     * Memastikan URI benar-benar ada (dapat diakses).
     *
     * @return self
     * @throws UriException
     */
    public function mustExist(): self
    {
        $this->mustBeValid();

        $headers = @get_headers($this->uri);

        if ($headers === false) {
            throw UriException::notFound($this->uri);
        }

        $statusCode = intval(substr($headers[0], 9, 3));

        if ($statusCode >= 400) {
            throw UriException::notFound($this->uri, $statusCode);
        }

        return $this;
    }

    /**
     * Memastikan URI valid.
     *
     * @return self
     * @throws UriException
     */
    public function mustBeValid(): self
    {
        if (!filter_var($this->uri, FILTER_VALIDATE_URL)) {
            throw UriException::invalid($this->uri);
        }

        return $this;
    }

    /**
     * Memastikan URI memiliki skema yang valid.
     *
     * @param array $allowed Skema yang diizinkan (opsional)
     * @return self
     * @throws UriException
     */
    public function mustHaveValidScheme(array $allowed = []): self
    {
        $this->mustBeValid();

        $scheme = parse_url($this->uri, PHP_URL_SCHEME);

        if ($scheme === null || $scheme === false) {
            throw UriException::malformed($this->uri, 'scheme');
        }

        if (!empty($allowed) && !in_array($scheme, $allowed)) {
            throw UriException::invalidScheme($this->uri, $scheme, $allowed);
        }

        return $this;
    }

    /**
     * Memastikan URI memiliki skema yang didukung.
     *
     * @param array $supported Skema yang didukung (opsional, default menggunakan skema umum)
     * @return self
     * @throws UriException
     */
    public function mustHaveSupportedScheme(array $supported = []): self
    {
        $this->mustBeValid();

        $scheme = parse_url($this->uri, PHP_URL_SCHEME);

        if ($scheme === null || $scheme === false) {
            throw UriException::malformed($this->uri, 'scheme');
        }

        $supportedSchemes = empty($supported) ? self::COMMON_SCHEMES : $supported;

        if (!in_array($scheme, $supportedSchemes)) {
            throw UriException::unsupportedScheme($this->uri, $scheme);
        }

        return $this;
    }

    /**
     * Memastikan URI memiliki host yang valid.
     *
     * @return self
     * @throws UriException
     */
    public function mustHaveValidHost(): self
    {
        $this->mustBeValid();

        $host = parse_url($this->uri, PHP_URL_HOST);

        if ($host === null || $host === false) {
            throw UriException::malformed($this->uri, 'host');
        }

        return $this;
    }

    /**
     * Memastikan URI memiliki path yang valid.
     *
     * @return self
     * @throws UriException
     */
    public function mustHaveValidPath(): self
    {
        $this->mustBeValid();

        $path = parse_url($this->uri, PHP_URL_PATH);

        if ($path === null || $path === false) {
            throw UriException::malformed($this->uri, 'path');
        }

        return $this;
    }

    /**
     * Memastikan URI memiliki query string yang valid.
     *
     * @return self
     * @throws UriException
     */
    public function mustHaveValidQuery(): self
    {
        $this->mustBeValid();

        $query = parse_url($this->uri, PHP_URL_QUERY);

        if ($query === null || $query === false) {
            throw UriException::malformed($this->uri, 'query');
        }

        return $this;
    }

    /**
     * Memastikan URI memiliki fragment yang valid.
     *
     * @return self
     * @throws UriException
     */
    public function mustHaveValidFragment(): self
    {
        $this->mustBeValid();

        $fragment = parse_url($this->uri, PHP_URL_FRAGMENT);

        if ($fragment === null || $fragment === false) {
            throw UriException::malformed($this->uri, 'fragment');
        }

        return $this;
    }

    /**
     * Memastikan URI menggunakan HTTPS.
     *
     * @return self
     * @throws UriException
     */
    public function mustBeSecure(): self
    {
        $this->mustBeValid();

        $scheme = parse_url($this->uri, PHP_URL_SCHEME);

        if ($scheme !== 'https') {
            throw UriException::invalidScheme($this->uri, $scheme ?? 'none', ['https']);
        }

        return $this;
    }

    /**
     * Mendapatkan komponen URI.
     *
     * @return array
     * @throws UriException
     */
    public function getComponents(): array
    {
        $this->mustBeValid();

        $components = parse_url($this->uri);

        if ($components === false) {
            throw UriException::malformed($this->uri, 'entire URI');
        }

        return $components;
    }
}
