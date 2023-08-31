<?php

namespace Limenius\ReactRenderer\Renderer;

interface RenderResultInterface {
    public function result(): string;
    public function consoleReplayScript(): string;
    public function hasErrors(): bool;
}
