<?php

namespace Limenius\ReactRenderer\Twig;

interface ComponentInterface {
    public function name(): string;
    public function props(): array;
    public function domId(): string;
    public function trace(): bool;
}
