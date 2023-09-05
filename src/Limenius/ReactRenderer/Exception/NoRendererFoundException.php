<?php

namespace Limenius\ReactRenderer\Exception;

use OutOfRangeException;

class NoRendererFoundException extends OutOfRangeException
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('No renderer service (tagged: limenius.renderer) was defined.');
    }
}
