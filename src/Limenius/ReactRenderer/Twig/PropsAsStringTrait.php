<?php

namespace Limenius\ReactRenderer\Twig;

use Limenius\ReactRenderer\Exception\PropsEncodeException;

/**
 * Provides the props as a string to be used in HTML or JavaScript.
 */
trait PropsAsStringTrait
{
    protected array $props = [];

    /**
     * Converts the props array to a string.
     *
     * @return string The JSON encoded props array.
     *
     * @throws PropsEncodeException
     */
    public function propsAsString(): string
    {
        $json = json_encode($this->props);

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
}
