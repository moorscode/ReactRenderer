<?php

namespace Limenius\ReactRenderer\Twig;

interface OptionsInterface
{
    public const SERVER_SIDE_RENDERING = 1;
    public const CLIENT_SIDE_RENDERING = 2;
    public const SERVER_AND_CLIENT_SIDE_RENDERING = self::SERVER_SIDE_RENDERING | self::CLIENT_SIDE_RENDERING;

    public function props(): array;

    /**
     * @return self::SERVER_SIDE_RENDERING|self::CLIENT_SIDE_RENDERING|self::SERVER_AND_CLIENT_SIDE_RENDERING|null
     */
    public function rendering(): ?int;

    public function cached(): bool;

    public function buffered(): bool;

    public function cacheKey(): string;

    /**
     * @return bool|null
     */
    public function trace(): ?bool;
}
