<?php

namespace Limenius\ReactRenderer\Factory;

use Limenius\ReactRenderer\Renderer\ReactRendererInterface;
use Psr\Log\LoggerInterface;

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
    private LoggerInterface $logger;

    /**
     * @param ReactRendererInterface[] $taggedServices
     */
    public function __construct(iterable $taggedServices, LoggerInterface $logger)
    {
        $this->taggedServices = $taggedServices;
        $this->logger = $logger;
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
        $renderer = iterator_to_array($this->taggedServices->getIterator())[0];
        $this->logger->debug(var_export($this->taggedServices->getIterator(), true));
        $this->logger->debug(var_export($renderer, true));

        if ($this->serverBundlePath && method_exists($renderer, 'setServerBundlePath')) {
            $renderer->setServerBundlePath($this->serverBundlePath);
        }

        if ($this->serverSocketPath && method_exists($renderer, 'setServerSocketPath')) {
            $renderer->setServerSocketPath($this->serverSocketPath);
        }

        return $renderer;
    }
}
