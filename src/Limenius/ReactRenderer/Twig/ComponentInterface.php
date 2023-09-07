<?php

namespace Limenius\ReactRenderer\Twig;

/**
 * Signature of a component.
 */
interface ComponentInterface
{
    public const SERVER_SIDE_RENDERING = 1;
    public const CLIENT_SIDE_RENDERING = 2;
    public const SERVER_AND_CLIENT_SIDE_RENDERING = self::SERVER_SIDE_RENDERING | self::CLIENT_SIDE_RENDERING;

    /**
     * The name of the component.
     *
     * @return string
     */
    public function name(): string;

    /**
     * The props of the component.
     *
     * @return array
     */
    public function props(): array;

    /**
     * The props in string format for HTML/JavaScript output.
     *
     * @return string
     */
    public function propsAsString(): string;

    /**
     * The rendering type of the component.
     *
     * @return self::SERVER_SIDE_RENDERING|self::CLIENT_SIDE_RENDERING|self::SERVER_AND_CLIENT_SIDE_RENDERING
     */
    public function rendering(): int;

    /**
     * Indicates if this component should be retrieved from cache.
     *
     * @return bool
     */
    public function cached(): bool;

    /**
     * Indicates if this component should be rendered in the buffered section (via *react_flush_buffer).
     *
     * @return bool
     */
    public function buffered(): bool;

    /**
     * Custom cache key to use to cache the component.
     *
     * @return string
     */
    public function cacheKey(): string;

    /**
     * Indicates if the output should be verbose.
     *
     * @return bool
     */
    public function trace(): bool;
}
