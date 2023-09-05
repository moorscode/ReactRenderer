<?php

namespace Limenius\ReactRenderer\Renderer;

/**
 * RenderResult implementation.
 */
class RenderResult implements RenderResultInterface
{
    private string $evaluated;
    private string $consoleReplay;
    private bool $hasErrors;

    /**
     * Constructor.
     *
     * @param string $evaluated     The evaluated component output.
     * @param string $consoleReplay The script that replays the console output.
     * @param bool   $hasErrors     Flag if the component rendered with errors.
     */
    public function __construct(
        string $evaluated,
        string $consoleReplay,
        bool $hasErrors
    ) {
        $this->evaluated = $evaluated;
        $this->consoleReplay = $consoleReplay;
        $this->hasErrors = $hasErrors;
    }

    /**
     * The render result.
     *
     * @return string
     */
    public function result(): string
    {
        return $this->evaluated;
    }

    /**
     * The script to replay the console output.
     *
     * @return string
     */
    public function consoleReplayScript(): string
    {
        return $this->consoleReplay;
    }

    /**
     * Flag that indicates if there where errors during rendering.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }
}
