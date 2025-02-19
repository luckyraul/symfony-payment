<?php

namespace Mygento\Payment\Api;

use Mygento\Payment\Service\Management;
use Mygento\Payment\Model\PaymentInfo;
use Mygento\Payment\Entity\Registration;
use Mygento\Payment\Entity\Transaction;

interface AdapterInterface
{
    public static function getCode(): string;

    public function getService(): Management;

    public function supportsTwoStepPayment(): bool;

    public function isTwoStepPayment(): bool;

    public function sale(string $method, InvoiceInterface $invoice): ?Registration;

    public function canSale(OrderInterface $order): bool;

    public function refund(string $paymentIdentifier, string $amount, string $currency, Transaction $parent): void;

    public function canRefund(string $paymentIdentifier): bool;

    public function authorize(string $method, InvoiceInterface $invoice): ?Registration;

    public function capture(string $paymentIdentifier, string $amount, string $currency, ?Transaction $parent = null): void;

    public function canCapture(string $paymentIdentifier): bool;

    public function void(string $paymentIdentifier, string $amount, string $currency, Transaction $parent): void;

    public function canVoid(string $paymentIdentifier): bool;

    public function canCheck(): bool;

    public function check(string $paymentIdentifier): PaymentInfo;

    public function registerCaptureNotification(string $amount): void;

    public function registerAuthorizeNotification(string $amount): void;

    public function registerVoidNotification(string $amount): void;

    public function registerRefundNotification(string $amount): void;

    public function registerSaleNotification(string $amount): void;
}
