<?php

namespace Limenius\ReactRenderer\Context;

/**
 * The context implementation.
 */
class Context implements ContextInterface
{
    /** @var bool */
    private $serverSide;
    /** @var string */
    private $href;
    /** @var string */
    private $requestUri;
    /** @var string */
    private $scheme;
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $baseUrl;
    /** @var string */
    private $pathInfo;
    /** @var string */
    private $queryString;

    /**
     * Constructor.
     *
     * @param bool   $serverSide
     * @param string $href
     * @param string $requestUri
     * @param string $scheme
     * @param string $host
     * @param int    $port
     * @param string $baseUrl
     * @param string $pathInfo
     * @param string $queryString
     */
    public function __construct(
        bool $serverSide,
        string $href,
        string $requestUri,
        string $scheme,
        string $host,
        int $port,
        string $baseUrl,
        string $pathInfo,
        string $queryString
    ) {
        $this->serverSide = $serverSide;
        $this->href = $href;
        $this->requestUri = $requestUri;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->baseUrl = $baseUrl;
        $this->pathInfo = $pathInfo;
        $this->queryString = $queryString;
    }

    /**
     * Indicates if we're rending server or client side.
     *
     * @return bool
     */
    public function isServerSide(): bool
    {
        return $this->serverSide;
    }

    /**
     * The current href.
     *
     * @return string
     */
    public function href(): string
    {
        return $this->href;
    }

    /**
     * The current request uri.
     *
     * @return string
     */
    public function requestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * The current scheme.
     *
     * @return string
     */
    public function scheme(): string
    {
        return $this->scheme;
    }

    /**
     * The current host.
     *
     * @return string
     */
    public function host(): string
    {
        return $this->host;
    }

    /**
     * The current port.
     *
     * @return int
     */
    public function port(): int
    {
        return $this->port;
    }

    /**
     * The current base url.
     *
     * @return string
     */
    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * The current path info.
     *
     * @return string
     */
    public function pathInfo(): string
    {
        return $this->pathInfo;
    }

    /**
     * The current query string.
     *
     * @return string
     */
    public function queryString(): string
    {
        return $this->queryString;
    }
}
