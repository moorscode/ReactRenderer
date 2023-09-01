<?php

namespace Limenius\ReactRenderer\Factory;

use Limenius\ReactRenderer\Renderer\ReactRendererInterface;

/**
 * Provides the configured renderer in the system.
 */
class RendererFactory
{
    /** @var ReactRendererInterface[] */
    private $taggedServices;
    /** @var string */
    private $serverSocketPath;
    /** @var string */
    private $serverBundlePath;

    /**
     * @param ReactRendererInterface[] $taggedServices
     */
    public function __construct(array $taggedServices)
    {
        $this->taggedServices = $taggedServices;
    }

    public function setServerSocketPath(string $serverSocketPath): void
    {
        $this->serverSocketPath = $serverSocketPath;
    }

    public function setServerBundlePath(string $serverBundlePath): void
    {
        $this->serverBundlePath = $serverBundlePath;
    }

    /**
     * Provides the first renderer.
     *
     * @return ReactRendererInterface
     */
    public function getRenderer(): ReactRendererInterface
    {
        $renderer = $this->taggedServices[0];
        if ($this->serverBundlePath && method_exists($renderer, 'setServerBundlePath')) {
            $renderer->setServerBundlePath($this->serverBundlePath);
        }

        if ($this->serverSocketPath && method_exists($renderer, 'setServerSocketPath')) {
            $renderer->setServerSocketPath($this->serverSocketPath);
        }

        return $this->taggedServices[0];
    }
}
