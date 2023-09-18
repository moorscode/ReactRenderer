<?php

namespace MyOnlineStore\ReactRenderer\Twig;

/**
 * The component.
 */
class Component implements ComponentInterface
{
    use PropsAsStringTrait;

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
        private readonly string $name,
        array $props,
        private readonly int $rendering,
        private readonly string $cacheKey = '',
        private readonly bool $cached = false,
        private readonly bool $buffered = false,
        private readonly bool $trace = false
    ) {
        $this->props = $props;

        assert(
            in_array(
                $rendering,
                [
                    self::SERVER_SIDE_RENDERING,
                    self::CLIENT_SIDE_RENDERING,
                    self::SERVER_AND_CLIENT_SIDE_RENDERING,
                ]
            )
        );
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
