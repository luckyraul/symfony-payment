<?php

namespace Mygento\Payment\Model;

class PaymentInfo
{
    public function __construct(
        public string $amountAuthorised,
        public string $amountPaid,
        public string $amountOrdered,
        public string $amountCanceled,
        public string $amountRefunded,
    ) {}
}
