<?php

namespace Limenius\ReactRenderer\Factory;

use Limenius\ReactRenderer\Exception\NoRendererFoundException;
use Limenius\ReactRenderer\Renderer\ReactRendererInterface;

/**
 * Provides the configured renderer in the system.
 */
class RendererFactory
{

    /**
     * @param ReactRendererInterface[] $taggedServices
     */
    public function __construct(private readonly iterable $taggedServices)
    {
    }

    /**
     * Provides the first renderer.
     *
     * @return ReactRendererInterface
     *
     * @throws NoRendererFoundException
     */
    public function getRenderer(): ReactRendererInterface
    {
        $renderers = iterator_to_array($this->taggedServices->getIterator());

        if (count($renderers) === 0) {
            throw new NoRendererFoundException();
        }

        // Pick a random renderer, this spreads the load evenly.
        $key = array_rand($renderers);

        return $renderers[$key];
    }
}
