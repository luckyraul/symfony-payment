<?php

namespace Mygento\Payment;

use Mygento\Payment\Model\TransactionSummary;
use Mygento\Payment\Model\PaymentInfo;

class Management
{
    /**
     * @param \Traversable<string,Api\AdapterInterface> $adapters
     */
    public function __construct(
        private Service\Basic $basic,
        private Service\Redirect $redirect,
        private Repository\RegistrationRepository $regRepo,
        private iterable $adapters,
    ) {}

    /**
     * @return Entity\Registration[]
     */
    public function getRegistrationsToCheck(): array
    {
        return $this->regRepo->getPending();
    }

    public function getPaymentUrl(string $code, string $orderId): ?string
    {
        if ($this->isPaid($code, $orderId)) {
            return null;
        }
        if ($this->isRefunded($code, $orderId)) {
            return null;
        }

        return $this->redirect->findRedirectKey($code, $orderId) ?? $this->redirect->createRedirectKey($code, $orderId);
    }

    public function getPaymentAmountStatus(string $code, string $orderId): TransactionSummary
    {
        return $this->basic->getTransactionSummaryByOrder($code, $orderId);
    }

    public function isPaid(string $code, string $orderId): bool
    {
        $summary = $this->basic->getTransactionSummaryByOrder($code, $orderId);
        if ($summary->auth > 0) {
            return true;
        }
        // Refunded is paid also
        if ($summary->capture > 0) {
            return true;
        }

        return false;
    }

    public function isRefunded(string $code, string $orderId): bool
    {
        $summary = $this->basic->getTransactionSummaryByOrder($code, $orderId);
        if ($summary->refund > 0) {
            return true;
        }

        return false;
    }

    public function check(string $code, string $orderId): ?PaymentInfo
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter || !$adapter->canCheck()) {
            throw new \Exception('no payment adapter available');
        }

        if (!$adapter->supportsRegistration()) {
            return $adapter->check($orderId);
        }

        $reg = $this->regRepo->findOneBy(['code' => $code, 'order' => $orderId]);

        if (!$reg || !$reg->getPaymentIdentifier()) {
            return null;
        }

        return $adapter->check($reg->getPaymentIdentifier());
    }

    public function checkPayment(string $code, string $paymentIdentifier): PaymentInfo
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter || !$adapter->canCheck()) {
            throw new \Exception('no payment adapter available');
        }

        return $adapter->check($paymentIdentifier);
    }

    public function pay(string $code, string $method, Api\InvoiceInterface $invoice): Model\Result
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter) {
            throw new \Exception('no payment adapter available');
        }

        $reg = $this->regRepo->findOneBy(['code' => $code, 'order' => $invoice->getOrder()]);
        $isPreSaved = (bool) $invoice->getRecurrentPaymentIdentifier();
        if (!$reg || !$reg->getPaymentIdentifier()) {
            $reg = $this->createPayment($adapter, $method, $invoice);

            if (!$reg || !$reg->getPaymentIdentifier()) {
                return new Model\Result(null, false, null);
            }
            if (!$isPreSaved) {
                $url = $this->redirect->createRedirectKey($code, $invoice->getOrder());

                return new Model\Result($url, false, null);
            }
        }

        $result = $this->check($code, $reg->getPaymentIdentifier());
        if (!$result) {
            return new Model\Result(null, false, null);
        }

        $final = 1 === bccomp($result->amountPaid, '0') && 0 === bccomp($result->amountRefunded, '0');

        $reccuring = $this->checkForSavedTransaction($code, $reg->getPaymentIdentifier());

        if ($final) {
            return new Model\Result(null, $final, $reccuring);
        }
        $url = $this->getPaymentUrl($code, $invoice->getOrder());

        return new Model\Result($url, false, $reccuring);
    }

    public function refund(string $code, string $orderId): void
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter) {
            throw new \Exception('no payment adapter available');
        }

        $reg = $this->regRepo->findOneBy(['code' => $code, 'order' => $orderId]);

        if (!$reg || !$reg->getPaymentIdentifier()) {
            throw new \Exception('no payment found');
        }

        $paymentIdentifier = $reg->getPaymentIdentifier();

        if (!$adapter->canRefund($paymentIdentifier)) {
            return;
        }

        $transaction = $this->basic->findCaptureTransaction($code, $paymentIdentifier);
        if (!$transaction) {
            throw new \Exception('capture transaction not found');
        }

        $adapter->refund($paymentIdentifier, $transaction->getAmount(), $transaction->getCurrency(), $transaction);
    }

    public function void(string $code, string $paymentIdentifier): void
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter) {
            throw new \Exception('no payment adapter available');
        }

        if (!$adapter->canVoid($paymentIdentifier)) {
            return;
        }

        $transaction = $this->basic->findAuthTransaction($code, $paymentIdentifier);
        if (!$transaction) {
            throw new \Exception('auth transaction not found');
        }
        $adapter->void($paymentIdentifier, $transaction->getAmount(), $transaction->getCurrency(), $transaction);
    }

    public function capture(string $code, string $paymentIdentifier): void
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter) {
            throw new \Exception('no payment adapter available');
        }

        if (!$adapter->canCapture($paymentIdentifier)) {
            return;
        }

        $transaction = $this->basic->findAuthTransaction($code, $paymentIdentifier);
        if (!$transaction) {
            throw new \Exception('auth transaction not found');
        }
        $adapter->capture($paymentIdentifier, $transaction->getAmount(), $transaction->getCurrency(), $transaction);
    }

    public function getAdapter(string $code): ?Api\AdapterInterface
    {
        $list = iterator_to_array($this->adapters, true);

        return $list[$code] ?? null;
    }

    private function createPayment(Api\AdapterInterface $adapter, string $method, Api\InvoiceInterface $invoice): ?Entity\Registration
    {
        $twoStep = $adapter->supportsTwoStepPayment() && $adapter->isTwoStepPayment();

        return $twoStep ? $adapter->authorize($method, $invoice) : $adapter->sale($method, $invoice);
    }

    private function checkForSavedTransaction(string $code, string $paymentId): ?string
    {
        $transaction = $this->basic->findCaptureTransaction($code, $paymentId);

        if ($transaction && $transaction->getSavedPaymentIdentity()) {
            return $transaction->getSavedPaymentIdentity();
        }

        return null;
    }
}
