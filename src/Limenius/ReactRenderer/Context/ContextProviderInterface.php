<?php

namespace Limenius\ReactRenderer\Context;

/**
 * Interface ContextProviderInterface
 *
 * Provides context
 */
interface ContextProviderInterface
{
    /**
     * getContext
     *
     * @param boolean $serverSide whether is this a server side context
     *
     * @return ContextInterface
     */
    public function getContext(bool $serverSide): ContextInterface;
}
