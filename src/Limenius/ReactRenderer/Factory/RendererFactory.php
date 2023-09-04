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
    private ?string $serverSocketPath;
    private ?string $serverBundlePath;
    private LoggerInterface $logger;

    /**
     * @param ReactRendererInterface[] $taggedServices
     * @param string|null              $serverBundlePath
     * @param string|null              $serverSocketPath
     * @param LoggerInterface          $logger
     */
    public function __construct(
        iterable $taggedServices,
        ?string $serverBundlePath,
        ?string $serverSocketPath,
        LoggerInterface $logger
    ) {
        $this->taggedServices = $taggedServices;
        $this->logger = $logger;
        $this->serverSocketPath = $serverSocketPath;
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

        if ($this->serverBundlePath && method_exists($renderer, 'setServerBundlePath')) {
            $renderer->setServerBundlePath($this->serverBundlePath);
        }

        if ($this->serverSocketPath && method_exists($renderer, 'setServerSocketPath')) {
            $renderer->setServerSocketPath($this->serverSocketPath);
        }

        return $renderer;
    }
}
