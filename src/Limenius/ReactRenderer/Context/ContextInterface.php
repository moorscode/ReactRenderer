<?php

namespace Limenius\ReactRenderer\Context;

interface ContextInterface {
    public function isServerSide(): bool;

    public function href(): string;

    public function requestUri(): string;

    public function scheme(): string;

    public function host(): string;

    public function port(): int;

    public function baseUrl(): string;

    public function pathInfo(): string;

    public function queryString(): string;
}
