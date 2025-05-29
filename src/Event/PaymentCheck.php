<?php

namespace Mygento\Payment\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Mygento\Payment\Model\PaymentInfo;

class PaymentCheck extends Event
{
    public function __construct(private string $orderId, private PaymentInfo $info) {}

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getInfo(): PaymentInfo
    {
        return $this->info;
    }
}
