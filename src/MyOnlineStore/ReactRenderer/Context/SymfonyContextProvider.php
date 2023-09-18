<?php

namespace MyOnlineStore\ReactRenderer\Context;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ContextProvider
 *
 * Extracts context information from a Symfony Request
 */
class SymfonyContextProvider implements ContextProviderInterface
{

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * getContext
     *
     * @param boolean $serverSide whether is this a server side context
     *
     * @return ContextInterface the context information
     */
    public function getContext(bool $serverSide): ContextInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        return new Context(
            $serverSide,
            $request->getSchemeAndHttpHost().$request->getRequestUri(),
            $request->getRequestUri(),
            $request->getScheme(),
            $request->getHost(),
            $request->getPort(),
            $request->getBaseUrl(),
            $request->getPathInfo(),
            $request->getQueryString() || ''
        );
    }
}
