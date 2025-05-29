<?php

namespace Mygento\Payment;

use Mygento\Payment\Model\PaymentInfo;

class PaymentManager
{
    /**
     * @param \Traversable<string,Api\AdapterInterface> $adapters
     */
    public function __construct(
        private Repository\TransactionRepository $transRepo,
        private Repository\RegistrationRepository $regRepo,
        private Service\Management $service,
        private iterable $adapters,
    ) {}

    public function getPaymentUrl(string $code, string $orderId): string
    {
        return $this->service->findRedirectKey($code, $orderId) ?? $this->service->createRedirectKey($code, $orderId);
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
                $url = $this->service->createRedirectKey($code, $invoice->getOrder());

                return new Model\Result($url, false, null);
            }
        }

        $result = $this->check($code, $reg->getPaymentIdentifier());

        $final = 1 === bccomp($result->amountPaid, '0') && 0 === bccomp($result->amountRefunded, '0');

        $reccuring = $this->checkForSavedTransaction($code, $reg->getPaymentIdentifier());

        if ($final) {
            return new Model\Result(null, $final, $reccuring);
        }
        $url = $this->getPaymentUrl($code, $invoice->getOrder());

        return new Model\Result($url, false, $reccuring);
    }

    public function check(string $code, string $paymentIdentifier): PaymentInfo
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter || !$adapter->canCheck()) {
            throw new \Exception('no payment adapter available');
        }

        return $adapter->check($paymentIdentifier);
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

        $transaction = $this->transRepo->findOneBy(
            [
                'code' => $code,
                'transactionType' => Entity\Transaction::AUTH,
                'paymentIdentifier' => $paymentIdentifier,
            ],
        );
        if (!$transaction) {
            throw new \Exception('auth transaction not found');
        }
        $adapter->capture($paymentIdentifier, $transaction->getAmount(), $transaction->getCurrency(), $transaction);
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

        $transaction = $this->transRepo->findOneBy(
            [
                'code' => $code,
                'transactionType' => Entity\Transaction::AUTH,
                'paymentIdentifier' => $paymentIdentifier,
            ],
        );
        if (!$transaction) {
            throw new \Exception('auth transaction not found');
        }
        $adapter->void($paymentIdentifier, $transaction->getAmount(), $transaction->getCurrency(), $transaction);
    }

    public function refund(string $code, string $paymentIdentifier): void
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter) {
            throw new \Exception('no payment adapter available');
        }

        if (!$adapter->canRefund($paymentIdentifier)) {
            return;
        }

        $transaction = $this->transRepo->findOneBy(
            [
                'code' => $code,
                'transactionType' => Entity\Transaction::CAPTURE,
                'paymentIdentifier' => $paymentIdentifier,
            ],
        );
        if (!$transaction) {
            throw new \Exception('caoture transaction not found');
        }

        $adapter->refund($paymentIdentifier, $transaction->getAmount(), $transaction->getCurrency(), $transaction);
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
        $transaction = $this->transRepo->findOneBy(
            [
                'code' => $code,
                'transactionType' => Entity\Transaction::CAPTURE,
                'paymentIdentifier' => $paymentId,
            ],
        );

        if ($transaction && $transaction->getSavedPaymentIdentity()) {
            return $transaction->getSavedPaymentIdentity();
        }

        return null;
    }
}
