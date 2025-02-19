<?php

namespace Mygento\Payment;

class Config
{
    public function __construct(
        private ?string $callbackRedirect = null,
    ) {}

    public function getCallbackRedirect(): ?string
    {
        return $this->callbackRedirect;
    }
}
