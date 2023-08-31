<?php

namespace Limenius\ReactRenderer\Twig;

use Limenius\ReactRenderer\Context\ContextProviderInterface;
use Limenius\ReactRenderer\Exception\PropsEncodeException;
use Limenius\ReactRenderer\Renderer\ReactRendererInterface;
use Limenius\ReactRenderer\Renderer\RenderResultInterface;
use Psr\Cache\CacheItemPoolInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ReactRenderExtension extends AbstractExtension
{
    protected $renderServerSide = false;
    protected $renderClientSide = false;
    protected $registeredStores = [];
    protected $needsToSetRailsContext = true;

    /** @var ReactRendererInterface|null */
    private $renderer;
    /** @var ContextProviderInterface */
    private $contextProvider;
    /** @var bool */
    private $trace;
    /** @var array */
    private $buffer = [];
    /** @var CacheItemPoolInterface|null */
    private $cache;
    /** @var string */
    private $twigFunctionPrefix;
    /** @var string */
    private $domIdPrefix;

    public function __construct(
        ReactRendererInterface   $renderer = null,
        ContextProviderInterface $contextProvider,
        int                      $defaultRendering,
        string                   $twigFunctionPrefix = '',
        string                   $domIdPrefix = 'sfreact',
        bool                     $trace = false
    )
    {
        $this->renderer = $renderer;
        $this->contextProvider = $contextProvider;
        $this->twigFunctionPrefix = $twigFunctionPrefix;
        $this->domIdPrefix = $domIdPrefix;
        $this->trace = $trace;

        switch ($defaultRendering) {
            case OptionsInterface::SERVER_SIDE_RENDERING:
                $this->renderClientSide = false;
                $this->renderServerSide = true;
                break;
            case OptionsInterface::CLIENT_SIDE_RENDERING:
                $this->renderClientSide = true;
                $this->renderServerSide = false;
                break;
            case OptionsInterface::SERVER_AND_CLIENT_SIDE_RENDERING:
            default:
                $this->renderClientSide = true;
                $this->renderServerSide = true;
                break;
        }
    }

    public function getName(): string
    {
        return 'react_render_extension';
    }

    public function setCache(CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction($this->twigFunctionPrefix . 'react_component', [$this, 'reactRenderComponent'], ['is_safe' => ['html']]),
            new TwigFunction($this->twigFunctionPrefix . 'redux_store', [$this, 'reactReduxStore'], ['is_safe' => ['html']]),
            new TwigFunction($this->twigFunctionPrefix . 'react_flush_buffer', [$this, 'reactFlushBuffer'], ['is_safe' => ['html']]),
        ];
    }

    public function reactRenderComponent(string $componentName, OptionsInterface $options): array
    {
        $str = '';

        $component = new Component(
            $componentName,
            $options->props(),
            $this->domIdPrefix . '-' . uniqid('reactRenderer', true),
            $options['trace'] ?? $this->trace
        );

        if ($this->shouldRenderClientSide($options)) {
            $str .= $this->createClientSideComponent($component, $options);
        }

        $str .= '<div id="' . $component->domId() . '">';

        if ($this->shouldRenderServerSide($options)) {
            $result = $this->serverSideRender($component, $options);

            $str .= $result->result();
            $str .= $result->consoleReplayScript();
        }
        $str .= '</div>';

        return $str;
    }

    public function reactReduxStore(string $storeName, $props): string
    {
        $propsString = is_array($props) ? $this->jsonEncode($props) : $props;
        $this->registeredStores[$storeName] = $propsString;

        $reduxStoreTag = sprintf(
            '<script type="application/json" data-js-react-on-rails-store="%s">%s</script>',
            $storeName,
            $propsString
        );

        return $this->renderContext() . $reduxStoreTag;
    }

    public function reactFlushBuffer(): string
    {
        $buffer = implode('', $this->buffer);

        $this->buffer = array();

        return $buffer;
    }

    private function shouldRenderServerSide(OptionsInterface $options): bool
    {
        if (is_null($options->rendering())) {
            return $this->renderServerSide;
        }

        return $options->rendering() ^ OptionsInterface::CLIENT_SIDE_RENDERING;
    }

    private function shouldRenderClientSide(OptionsInterface $options): bool
    {
        if (is_null($options->rendering())) {
            return $this->renderClientSide;
        }

        return $options->rendering() ^ OptionsInterface::SERVER_SIDE_RENDERING;
    }

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

    private function serverSideRender(ComponentInterface $component, OptionsInterface $options): RenderResultInterface
    {
        if ($options->cached()) {
            return $this->renderCached($component, $options);
        }

        return $this->doServerSideRender($component);
    }

    private function doServerSideRender(ComponentInterface $component): RenderResultInterface
    {
        return $this->renderer->render(
            $component->name(),
            $this->jsonEncode($component->props()),
            $component->domId(),
            $this->registeredStores,
            $component->trace()
        );
    }

    private function renderCached(ComponentInterface $component, OptionsInterface $options): RenderResultInterface
    {
        if ($this->cache === null) {
            return $this->doServerSideRender($component);
        }

        $cacheItem = $this->cache->getItem($component->name() . $this->getCacheKey($options, $component));
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $rendered = $this->doServerSideRender($component);

        $cacheItem->set($rendered);
        $this->cache->save($cacheItem);

        return $rendered;
    }

    private function getCacheKey(OptionsInterface $options, ComponentInterface $component): string
    {
        return ($options->cacheKey() ?: $component->name() . '_' . md5($this->jsonEncode($component->props()))) . '.rendered';
    }

    /**
     * @param ComponentInterface $component
     * @param OptionsInterface $options
     * @return string
     */
    private function createClientSideComponent(ComponentInterface $component, OptionsInterface $options): string
    {
        $output = $this->renderContext();
        $output .= sprintf(
            '<script type="application/json" class="js-react-on-rails-component" data-component-name="%s" data-dom-id="%s">%s</script>',
            $component->name(),
            $component->domId(),
            $this->jsonEncode($component->props())
        );

        if ($options->buffered()) {
            $this->buffer[] = $output;
            return '';
        }

        return $output;
    }
}
