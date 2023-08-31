<?php

namespace Limenius\ReactRenderer\Context;

class Context implements ContextInterface
{
    /**
     * @var bool
     */
    private $serverSide;
    /**
     * @var string
     */
    private $href;
    /**
     * @var string
     */
    private $requestUri;
    /**
     * @var string
     */
    private $scheme;
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var string
     */
    private $baseUrl;
    /**
     * @var string
     */
    private $pathInfo;
    /**
     * @var string
     */
    private $queryString;

    public function __construct(
        bool   $serverSide,
        string $href,
        string $requestUri,
        string $scheme,
        string $host,
        int    $port,
        string $baseUrl,
        string $pathInfo,
        string $queryString
    )
    {

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

    public function isServerSide(): bool
    {
        return $this->serverSide;
    }

    public function href(): string
    {
        return $this->href;
    }

    public function requestUri(): string
    {
        return $this->requestUri;
    }

    public function scheme(): string
    {
        return $this->scheme;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function pathInfo(): string
    {
        return $this->pathInfo;
    }

    public function queryString(): string
    {
        return $this->queryString;
    }
}
