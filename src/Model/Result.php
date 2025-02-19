<?php

namespace Mygento\Payment\Model;

class Result
{
    public function __construct(
        public ?string $redirectUrl = null,
        public ?string $recurringPaymentIdentifier = null,
    ) {}
}
