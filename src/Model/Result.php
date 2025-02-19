<?php

namespace Mygento\Payment\Model;

class Result
{
    public function __construct(
        public ?string $redirectUrl = null,
        public bool $isPaid = false,
        public ?string $recurringPaymentIdentifier = null,
    ) {}
}
