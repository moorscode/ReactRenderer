<?php

namespace MyOnlineStore\ReactRenderer\Context;

/**
 * Signature of the context.
 */
interface ContextInterface
{
    /**
     * Indicates if we want to render server side.
     *
     * @return bool
     */
    public function isServerSide(): bool;

    /**
     * The request Href.
     *
     * @return string
     */
    public function href(): string;

    /**
     * The request Uri.
     *
     * @return string
     */
    public function requestUri(): string;

    /**
     * The request Scheme.
     *
     * @return string
     */
    public function scheme(): string;

    /**
     * The request Host.
     *
     * @return string
     */
    public function host(): string;

    /**
     * The request Port.
     *
     * @return int
     */
    public function port(): int;

    /**
     * The request Base Url.
     *
     * @return string
     */
    public function baseUrl(): string;

    /**
     * The request Path Info.
     *
     * @return string
     */
    public function pathInfo(): string;

    /**
     * The request Query String.
     *
     * @return string
     */
    public function queryString(): string;
}
