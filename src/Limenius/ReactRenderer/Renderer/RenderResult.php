<?php

namespace Limenius\ReactRenderer\Renderer;

class RenderResult implements RenderResultInterface
{
    /**
     * @var string
     */
    private $evaluated;
    /**
     * @var string
     */
    private $consoleReplay;
    /**
     * @var bool
     */
    private $hasErrors;

    public function __construct(
        string $evaluated,
        string $consoleReplay,
        bool   $hasErrors
    )
    {

        $this->evaluated = $evaluated;
        $this->consoleReplay = $consoleReplay;
        $this->hasErrors = $hasErrors;
    }

    public function result(): string
    {
        return $this->evaluated;
    }

    public function consoleReplayScript(): string
    {
        return $this->consoleReplay;
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }
}
