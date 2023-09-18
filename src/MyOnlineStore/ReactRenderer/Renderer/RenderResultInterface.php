<?php

namespace MyOnlineStore\ReactRenderer\Renderer;

/**
 * Signature of the ReactRender result value object.
 */
interface RenderResultInterface
{
    /**
     * The result string, HTML or error message.
     *
     * @return string
     */
    public function result(): string;

    /**
     * The script that replays the output of the console for the component rendering.
     *
     * @return string
     */
    public function consoleReplayScript(): string;

    /**
     * Indicates if the component rendered with errors.
     *
     * @return bool
     */
    public function hasErrors(): bool;
}
