<?php

namespace Limenius\ReactRenderer\Twig;

class Options implements OptionsInterface
{
    /**
     * @var array
     */
    private $props;
    /**
     * @var int|null
     */
    private $rendering;
    /**
     * @var string
     */
    private $cacheKey;
    /**
     * @var bool
     */
    private $cached;
    /**
     * @var bool
     */
    private $buffered;
    /**
     * @var bool|null
     */
    private $trace;

    public function __construct(
        array  $props,
        int    $rendering = null,
        string $cacheKey = '',
        bool   $cached = false,
        bool   $buffered = false,
        bool   $trace = null
    )
    {

        $this->props = $props;
        $this->rendering = $rendering;
        $this->cacheKey = $cacheKey;
        $this->cached = $cached;
        $this->buffered = $buffered;
        $this->trace = $trace;
    }

    public function props(): array
    {
        return $this->props;
    }

    public function rendering(): ?int
    {
        return $this->rendering;
    }

    public function cached(): bool
    {
        return $this->cached;
    }

    public function buffered(): bool
    {
        return $this->buffered;
    }

    public function cacheKey(): string
    {
        return $this->cacheKey;
    }

    public function trace(): ?bool
    {
        return $this->trace;
    }
}
