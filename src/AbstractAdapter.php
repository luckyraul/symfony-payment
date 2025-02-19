<?php

namespace Mygento\Payment;

use Mygento\Payment\Service\Management;

abstract class AbstractAdapter implements Api\AdapterInterface
{
    public function __construct(private Management $service) {}

    public function getService(): Management
    {
        return $this->service;
    }

    public function canCapture(string $paymentIdentifier): bool
    {
        $summary = $this->getService()->getTransactionSummary(static::getCode(), $paymentIdentifier);

        return bccomp($summary->auth, '0') > 0
                && 0 === bccomp($summary->void, '0')
                && 0 === bccomp($summary->refund, '0')
                && 0 === bccomp($summary->capture, '0');
    }

    public function canVoid(string $paymentIdentifier): bool
    {
        $summary = $this->getService()->getTransactionSummary(static::getCode(), $paymentIdentifier);

        return bccomp($summary->auth, '0') > 0
                && 0 === bccomp($summary->void, '0')
                && 0 === bccomp($summary->refund, '0')
                && 0 === bccomp($summary->capture, '0');
    }

    public function canRefund(string $paymentIdentifier): bool
    {
        $summary = $this->getService()->getTransactionSummary(static::getCode(), $paymentIdentifier);

        return bccomp($summary->auth, '0') >= 0
                && 0 === bccomp($summary->void, '0')
                && 0 === bccomp($summary->refund, '0')
                && bccomp($summary->capture, '0') > 0;
    }
}
