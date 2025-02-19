<?php

namespace Mygento\Payment\Model;

class TransactionSummary
{
    public function __construct(
        public string $auth = '0',
        public string $capture = '0',
        public string $void = '0',
        public string $refund = '0',
    ) {}
}
