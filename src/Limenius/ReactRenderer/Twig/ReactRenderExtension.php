<?php

namespace Limenius\ReactRenderer\Twig;

use Limenius\ReactRenderer\Context\ContextProviderInterface;
use Limenius\ReactRenderer\Exception\PropsEncodeException;
use Limenius\ReactRenderer\Factory\RendererFactory;
use Limenius\ReactRenderer\Renderer\ReactRendererInterface;
use Limenius\ReactRenderer\Renderer\RenderResultInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The Symfony/Twig extension that bridges the gap between Twig and the Server Side Rendering engine.
 */
class ReactRenderExtension extends AbstractExtension
{
    protected bool $renderServerSide = false;
    protected bool $renderClientSide = false;
    protected array $registeredStores = [];
    protected bool $needsToSetRailsContext = true;

    private ReactRendererInterface $renderer;
    private ContextProviderInterface $contextProvider;
    private bool $trace;
    private array $buffer = [];
    private ?CacheItemPoolInterface $cache;
    private string $twigFunctionPrefix;
    private string $domIdPrefix;

    /**
     * Constructor.
     *
     * @param RendererFactory          $rendererFactory
     * @param ContextProviderInterface $contextProvider
     * @param int                      $defaultRendering
     * @param string                   $twigFunctionPrefix
     * @param string                   $domIdPrefix
     * @param bool                     $trace
     */
    public function __construct(
        RendererFactory $rendererFactory,
        ContextProviderInterface $contextProvider,
        int $defaultRendering,
        string $twigFunctionPrefix = '',
        string $domIdPrefix = 'sfreact',
        bool $trace = false
    ) {
        $this->renderer = $rendererFactory->getRenderer();
        $this->contextProvider = $contextProvider;
        $this->twigFunctionPrefix = $twigFunctionPrefix;
        $this->domIdPrefix = $domIdPrefix;
        $this->trace = $trace;

        switch ($defaultRendering) {
            case ComponentInterface::SERVER_SIDE_RENDERING:
                $this->renderClientSide = false;
                $this->renderServerSide = true;
                break;
            case ComponentInterface::CLIENT_SIDE_RENDERING:
                $this->renderClientSide = true;
                $this->renderServerSide = false;
                break;
            case ComponentInterface::SERVER_AND_CLIENT_SIDE_RENDERING:
            default:
                $this->renderClientSide = true;
                $this->renderServerSide = true;
                break;
        }
    }

    /**
     * The name of this extension.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'react_render_extension';
    }

    /**
     * Configure the cache pool to use.
     *
     * @param CacheItemPoolInterface $cache
     *
     * @return void
     */
    public function setCache(CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Provides the Twig functions that should be loaded.
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                $this->twigFunctionPrefix.'react_component',
                [$this, 'reactRenderComponent'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                $this->twigFunctionPrefix.'redux_store',
                [$this, 'reactReduxStore'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                $this->twigFunctionPrefix.'react_flush_buffer',
                [$this, 'reactFlushBuffer'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Creates the React component output to be used in the Twig template.
     *
     * @param string    $componentName
     * @param array     $props
     * @param int       $rendering
     * @param string    $cacheKey
     * @param bool      $cached
     * @param bool      $buffered
     * @param bool|null $trace
     *
     * @return string
     */
    public function reactRenderComponent(
        string $componentName,
        array $props = array(),
        int $rendering = ComponentInterface::SERVER_AND_CLIENT_SIDE_RENDERING,
        string $cacheKey = '',
        bool $cached = false,
        bool $buffered = false,
        ?bool $trace = null
    ): string {
        $domId = $this->domIdPrefix.'-'.uniqid('reactRenderer', true);

        $str = '';

        $component = new Component(
            $componentName,
            $props,
            $rendering,
            $cacheKey,
            $cached,
            $buffered,
            $trace ?? $this->trace
        );

        if ($this->shouldRenderClientSide($component)) {
            $str .= $this->createClientSideComponent($component, $domId);
        }

        $str .= '<div id="'.$domId.'">';

        if ($this->shouldRenderServerSide($component)) {
            $result = $this->serverSideRender($component, $domId);

            $str .= $result->result();
            $str .= $result->consoleReplayScript();
        }
        $str .= '</div>';

        return $str;
    }

    /**
     * Creates the Redux store output to be used in the Twig template.
     *
     * @param string $storeName
     * @param array  $props
     *
     * @return string
     */
    public function reactReduxStore(string $storeName, array $props): string
    {
        $propsString = $this->jsonEncode($props);
        $this->registeredStores[$storeName] = $propsString;

        $reduxStoreTag = sprintf(
            '<script type="application/json" data-js-react-on-rails-store="%s">%s</script>',
            $storeName,
            $propsString
        );

        return $this->renderContext().$reduxStoreTag;
    }

    /**
     * Flushes the buffered components to the Twig template.
     *
     * @return string
     */
    public function reactFlushBuffer(): string
    {
        $buffer = implode('', $this->buffer);

        $this->buffer = array();

        return $buffer;
    }

    /**
     * Indicates if the component should be rendered server side.
     *
     * @param ComponentInterface $component
     *
     * @return bool
     */
    private function shouldRenderServerSide(ComponentInterface $component): bool
    {
        if (is_null($component->rendering())) {
            return $this->renderServerSide;
        }

        return $component->rendering() ^ ComponentInterface::CLIENT_SIDE_RENDERING;
    }

    /**
     * Indicates if the component should be rendered client side.
     *
     * @param ComponentInterface $component
     *
     * @return bool
     */
    private function shouldRenderClientSide(ComponentInterface $component): bool
    {
        if (is_null($component->rendering())) {
            return $this->renderClientSide;
        }

        return $component->rendering() ^ ComponentInterface::SERVER_SIDE_RENDERING;
    }

    /**
     * Renders the context if not already rendered.
     *
     * @return string
     */
    private function renderContext(): string
    {
        if (!$this->needsToSetRailsContext) {
            return '';
        }

        $this->needsToSetRailsContext = false;

        $context = $this->contextProvider->getContext(false);
        $jsContext = $this->jsonEncode([
            'serverSide' => $context->isServerSide(),
            'href' => $context->href(),
            'location' => $context->requestUri(),
            'scheme' => $context->scheme(),
            'host' => $context->host(),
            'port' => $context->port(),
            'base' => $context->baseUrl(),
            'pathname' => $context->pathInfo(),
            'search' => $context->queryString(),
        ]);

        return sprintf(
            '<script type="application/json" id="js-react-on-rails-context">%s</script>',
            $jsContext
        );
    }

    /**
     * Safely encodes the input to a JSON string.
     *
     * @param mixed $input
     *
     * @return string
     */
    private function jsonEncode($input): string
    {
        $json = json_encode($input);

        if (json_last_error() !== 0) {
            throw new PropsEncodeException(
                sprintf(
                    'JSON could not be encoded, Error Message was %s',
                    json_last_error_msg()
                )
            );
        }

        return $json;
    }

    /**
     * Renders the component via the server side method.
     *
     * @param ComponentInterface $component
     * @param string             $domId
     *
     * @return RenderResultInterface
     */
    private function serverSideRender(ComponentInterface $component, string $domId): RenderResultInterface
    {
        if ($component->cached()) {
            return $this->renderCached($component, $domId);
        }

        return $this->doServerSideRender($component, $domId);
    }

    /**
     * Calls the server side render method.
     *
     * @param ComponentInterface $component
     * @param string             $domId
     *
     * @return RenderResultInterface
     */
    private function doServerSideRender(ComponentInterface $component, string $domId): RenderResultInterface
    {
        return $this->renderer->render(
            $component->name(),
            $component->propsAsString(),
            $domId,
            $this->registeredStores,
            $component->trace()
        );
    }

    /**
     * Caches the server side render output.
     *
     * @param ComponentInterface $component
     * @param string             $domId
     *
     * @return RenderResultInterface
     */
    private function renderCached(ComponentInterface $component, string $domId): RenderResultInterface
    {
        if ($this->cache === null) {
            return $this->doServerSideRender($component, $domId);
        }

        try {
            $cacheItem = $this->cache->getItem($this->getCacheKey($component));
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        } catch (InvalidArgumentException $exception) {
            // Do nothing.
        }

        $rendered = $this->doServerSideRender($component, $domId);

        if (isset($cacheItem)) {
            $cacheItem->set($rendered);
            $this->cache->save($cacheItem);
        }

        return $rendered;
    }

    /**
     * Provides the cache key to be used for the component.
     *
     * @param ComponentInterface $component
     *
     * @return string
     */
    private function getCacheKey(ComponentInterface $component): string
    {
        return $component->name().'_'.md5($component->propsAsString()).'.rendered';
    }

    /**
     * Renders the client side component.
     *
     * @param ComponentInterface $component
     * @param string             $domId
     *
     * @return string
     */
    private function createClientSideComponent(ComponentInterface $component, string $domId): string
    {
        $output = $this->renderContext();
        $output .= sprintf(
            '<script type="application/json" class="js-react-on-rails-component" data-component-name="%s" data-dom-id="%s">%s</script>',
            $component->name(),
            $domId,
            $component->propsAsString()
        );

        if ($component->buffered()) {
            $this->buffer[] = $output;

            return '';
        }

        return $output;
    }
}
