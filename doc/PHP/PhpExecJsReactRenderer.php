<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use MyOnlineStore\ReactRenderer\Context\ContextProviderInterface;
use MyOnlineStore\ReactRenderer\Renderer\ReactRendererInterface;
use MyOnlineStore\ReactRenderer\Renderer\RenderResult;
use MyOnlineStore\ReactRenderer\Renderer\RenderResultInterface;
use Nacmartin\PhpExecJs\PhpExecJs;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * This class should be in the implementation repository.
 *
 * Class PhpExecJsReactRenderer
 */
class PhpExecJsReactRenderer implements ReactRendererInterface
{
    protected ?PhpExecJs $phpExecJs;
    protected bool $needToSetContext = true;
    protected CacheItemPoolInterface $cache;
    protected string $cacheKey;

    /**
     * PhpExecJsReactRenderer constructor.
     *
     * @param string                   $serverBundlePath
     * @param bool                     $failLoud
     * @param ContextProviderInterface $contextProvider
     * @param LoggerInterface|null     $logger
     */
    public function __construct(
        protected string $serverBundlePath,
        protected bool $failLoud,
        private readonly ContextProviderInterface $contextProvider,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Sets the cache pool to be used.
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
     * @param PhpExecJs $phpExecJs
     */
    public function setPhpExecJs(PhpExecJs $phpExecJs): void
    {
        $this->phpExecJs = $phpExecJs;
    }

    /**
     * @param string $serverBundlePath
     */
    public function setServerBundlePath(string $serverBundlePath): void
    {
        $this->serverBundlePath = $serverBundlePath;
        $this->needToSetContext = true;
    }

    /**
     * @param string $componentName
     * @param string $propsString
     * @param string $uuid
     * @param array  $registeredStores
     * @param bool   $trace
     *
     * @return RenderResultInterface
     */
    public function render(
        string $componentName,
        string $propsString,
        string $uuid,
        array $registeredStores = array(),
        bool $trace = false
    ): RenderResultInterface {
        $this->ensurePhpExecJsIsBuilt();
        if ($this->needToSetContext) {
            if ($this->phpExecJs->supportsCache()) {
                $this->phpExecJs->setCache($this->cache);
            }
            $this->phpExecJs->createContext(
                $this->consolePolyfill()."\n".$this->timerPolyfills($trace)."\n".$this->loadServerBundle(),
                $this->cacheKey
            );
            $this->needToSetContext = false;
        }
        $result = json_decode(
            $this->phpExecJs->evalJs($this->wrap($componentName, $propsString, $uuid, $registeredStores, $trace)),
            true
        );
        if ($result['hasErrors']) {
            $this->logErrors($result['consoleReplayScript']);
            if ($this->failLoud) {
                $this->throwError($result['consoleReplayScript'], $componentName);
            }
        }

        return new RenderResult(
            $result['hasErrors'] ? $result['html'] : $result['html']['componentHtml'],
            $result['consoleReplayScript'],
            $result['hasErrors']
        );
    }

    /**
     * @return string
     */
    protected function consolePolyfill(): string
    {
        return <<<JS
var console = { history: [] };
['error', 'log', 'info', 'warn'].forEach(function (level) {
  console[level] = function () {
    var argArray = Array.prototype.slice.call(arguments);
    if (argArray.length > 0) {
      argArray[0] = '[SERVER] ' + argArray[0];
    }
    console.history.push({level: level, arguments: argArray});
  };
});
JS;
    }

    /**
     * @param array  $registeredStores
     * @param string $context
     *
     * @return string
     */
    protected function initializeReduxStores(array $registeredStores = array(), string $context = ''): string
    {
        if (!is_array($registeredStores) || empty($registeredStores)) {
            return '';
        }

        $result = 'var reduxProps, context, storeGenerator, store'.PHP_EOL;
        foreach ($registeredStores as $storeName => $reduxProps) {
            $result .= <<<JS
reduxProps = $reduxProps;
context = $context;
storeGenerator = ReactOnRails.getStoreGenerator('$storeName');
store = storeGenerator(reduxProps, context);
ReactOnRails.setStore('$storeName', store);
JS;
        }

        return $result;
    }

    /**
     * @param string $name
     * @param string $propsString
     * @param string $uuid
     * @param array  $registeredStores
     * @param bool   $trace
     *
     * @return string
     */
    protected function wrap(
        string $name,
        string $propsString,
        string $uuid,
        array $registeredStores = array(),
        bool $trace = false
    ): string {
        $context = $this->contextProvider->getContext(true);
        $contextArray = [
            'serverSide' => $context->isServerSide(),
            'href' => $context->href(),
            'location' => $context->requestUri(),
            'scheme' => $context->scheme(),
            'host' => $context->host(),
            'port' => $context->port(),
            'base' => $context->baseUrl(),
            'pathname' => $context->pathInfo(),
            'search' => $context->queryString(),
        ];

        $traceStr = $trace ? 'true' : 'false';
        $jsContext = json_encode($contextArray);
        $initializedReduxStores = $this->initializeReduxStores($registeredStores, $jsContext);

        return <<<JS
(function() {
  $initializedReduxStores
  return ReactOnRails.serverRenderReactComponent({
    name: '$name',
    domNodeId: '$uuid',
    props: $propsString,
    trace: $traceStr,
    railsContext: $jsContext,
  });
})();
JS;
    }

    /**
     * Loads the server bundle script.
     *
     * @return string
     */
    protected function loadServerBundle(): string
    {
        if (!$serverBundle = @file_get_contents($this->serverBundlePath)) {
            throw new \RuntimeException(sprintf('Server bundle not found in path: %s', $this->serverBundlePath));
        }

        return $serverBundle;
    }

    /**
     * Ensures some PhpExecJs instance is loaded.
     *
     * @return void
     */
    protected function ensurePhpExecJsIsBuilt()
    {
        if ($this->phpExecJs) {
            return;
        }

        $this->phpExecJs = new PhpExecJs();
    }

    /**
     * @param bool $trace
     *
     * @return string
     */
    protected function timerPolyfills(bool $trace): string
    {
        return <<<JS
function getStackTrace () {
  var stack;
  try {
    throw new Error('');
  }
  catch (error) {
    stack = error.stack || '';
  }
  stack = stack.split('\\n').map(function (line) { return line.trim(); });
  return stack.splice(stack[0] == 'Error' ? 2 : 1);
}

function setInterval() {
  {$this->undefinedForPhpExecJsLogging('setInterval', $trace)}
}

function setTimeout() {
  {$this->undefinedForPhpExecJsLogging('setTimeout', $trace)}
}

function clearTimeout() {
  {$this->undefinedForPhpExecJsLogging('clearTimeout', $trace)}
}
JS;
    }

    /**
     * @param string $functionName
     * @param bool   $trace
     *
     * @return string
     */
    protected function undefinedForPhpExecJsLogging(string $functionName, bool $trace): string
    {
        return !$trace ? '' : <<<JS
console.error(
  '"$functionName" is not defined for phpexecjs. https://github.com/nacmartin/phpexecjs#why-cant-i-use-some-functions-like-settimeout. ' +
  'Note babel-polyfill may call this.'
);
console.error(getStackTrace().join('\\n'));
JS;
    }

    /**
     * Logs the errors found in the console replay script.
     *
     * @param string $consoleReplay
     *
     * @return void
     */
    protected function logErrors(string $consoleReplay): void
    {
        if (!$this->logger) {
            return;
        }
        $report = $this->extractErrorLines($consoleReplay);
        foreach ($report as $line) {
            $this->logger->warning($line);
        }
    }

    /**
     * Extracts the error lines from the console replay script.
     *
     * @param string $consoleReplay
     *
     * @return array
     */
    protected function extractErrorLines(string $consoleReplay): array
    {
        $report = [];
        $lines = explode("\n", $consoleReplay);
        $usefulLines = array_slice($lines, 2, count($lines) - 4);
        foreach ($usefulLines as $line) {
            if (preg_match('/console\.error\.apply\(console, \["\[SERVER\] (?P<msg>.*)"\]\);/', $line, $matches)) {
                $report[] = $matches['msg'];
            }
        }

        return $report;
    }

    /**
     * Converts the console replay script errors into a PHP error.
     *
     * @param string $consoleReplay
     * @param string $componentName
     *
     * @return void
     *
     * @throws EvalJsException
     */
    protected function throwError(string $consoleReplay, string $componentName): void
    {
        $report = implode("\n", $this->extractErrorLines($consoleReplay));
        throw new EvalJsException($componentName, $report);
    }
}
