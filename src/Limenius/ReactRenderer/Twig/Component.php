<?php

namespace Limenius\ReactRenderer\Twig;

class Component implements ComponentInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $props;
    /**
     * @var string
     */
    private $domId;
    /**
     * @var bool
     */
    private $trace;

    public function __construct(
        string $name,
        array  $props,
        string $domId,
        bool   $trace = false
    )
    {

        $this->name = $name;
        $this->props = $props;
        $this->domId = $domId;
        $this->trace = $trace;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function props(): array
    {
        return $this->props;
    }

    public function domId(): string
    {
        return $this->domId;
    }

    public function trace(): bool
    {
        return $this->trace;
    }
}
