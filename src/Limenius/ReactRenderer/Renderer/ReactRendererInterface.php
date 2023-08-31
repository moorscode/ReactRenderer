<?php

namespace Limenius\ReactRenderer\Renderer;

interface ReactRendererInterface {
    /**
     * @param string $componentName
     * @param string $propsString
     * @param string $uuid
     * @param array $registeredStores
     * @param bool $trace
     *
     * @return RenderResultInterface
     */
    public function render(string $componentName, string $propsString, string $uuid, array $registeredStores = array(), bool $trace = false): RenderResultInterface;
}
