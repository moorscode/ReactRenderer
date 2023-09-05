<?php

namespace Limenius\ReactRenderer\Twig;

/**
 * The component.
 */
class Component implements ComponentInterface
{
    use PropsAsStringTrait;

    private string $name;
    private bool $trace;
    private int $rendering;
    private string $cacheKey;
    private bool $cached;
    private bool $buffered;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $props
     * @param int    $rendering
     * @param string $cacheKey
     * @param bool   $cached
     * @param bool   $buffered
     * @param bool   $trace
     */
    public function __construct(
        string $name,
        array $props,
        int $rendering,
        string $cacheKey = '',
        bool $cached = false,
        bool $buffered = false,
        bool $trace = false
    ) {
        $this->name = $name;
        $this->props = $props;
        $this->rendering = $rendering;
        $this->cacheKey = $cacheKey;
        $this->cached = $cached;
        $this->buffered = $buffered;
        $this->trace = $trace;
    }

    /**
     * The name of the component.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * The props of the component.
     *
     * @return array
     */
    public function props(): array
    {
        return $this->props;
    }

    /**
     * Indicates if we want to trace the rendering.
     *
     * @return bool
     */
    public function trace(): bool
    {
        return $this->trace;
    }

    /**
     * Indicates the rendering type of the component.
     *
     * @return self::SERVER_SIDE_RENDERING|self::CLIENT_SIDE_RENDERING|self::SERVER_AND_CLIENT_SIDE_RENDERING
     */
    public function rendering(): int
    {
        return $this->rendering;
    }

    /**
     * Indicates if the component should be fetched from cache.
     *
     * @return bool
     */
    public function cached(): bool
    {
        return $this->cached;
    }

    /**
     * Indicates if the component should be output via the buffered method.
     *
     * @return bool
     */
    public function buffered(): bool
    {
        return $this->buffered;
    }

    /**
     * Cache key to use when caching the component output.
     *
     * @return string
     */
    public function cacheKey(): string
    {
        return $this->cacheKey;
    }
}
