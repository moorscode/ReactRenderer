<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

/**
 * Class EvalJsException
 */
class EvalJsException extends \RuntimeException
{
    /**
     * EvalJsException constructor.
     *
     * @param string $componentName
     * @param int    $consoleReplay
     */
    public function __construct(string $componentName, int $consoleReplay)
    {
        $message = 'Error rendering component '.$componentName."\nConsole log:".$consoleReplay;
        parent::__construct($message);
    }
}
