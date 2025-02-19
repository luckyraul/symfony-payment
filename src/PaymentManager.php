<?php

namespace Mygento\Payment;

class PaymentManager
{
    /**
     * @param \Traversable<string,Api\AdapterInterface> $adapters
     */
    public function __construct(
        private Repository\TransactionRepository $transRepo,
        private Repository\RegistrationRepository $regRepo,
        private iterable $adapters,
    ) {}

    public function getAdapter(string $code): ?Api\AdapterInterface
    {
        $list = iterator_to_array($this->adapters, true);

        return $list[$code] ?? null;
    }

    public function pay(string $code, string $method, Api\InvoiceInterface $invoice): Model\Result
    {
        $url = null;
        $reccuring = null;

        $adapter = $this->getAdapter($code);
        if (!$adapter) {
            throw new \Exception('no payment adapter available');
        }

        $reg = $this->regRepo->findOneBy(['code' => $code, 'order' => $invoice->getOrder()]);
        $twoStep = $adapter->supportsTwoStepPayment() && $adapter->isTwoStepPayment();

        if (!$reg || null === $reg->getPaymentIdentifier()) {
            $url = $twoStep ? $adapter->authorize($method, $invoice) : $adapter->sale($method, $invoice);

            return new Model\Result($url, $reccuring);
        }

        $this->check($code, $reg->getPaymentIdentifier());

        $url = $twoStep ? $adapter->authorize($method, $invoice) : $adapter->sale($method, $invoice);

        return new Model\Result($url, $reccuring);
    }

    public function check(string $code, string $paymentIdentifier): void
    {
        $adapter = $this->getAdapter($code);
        if (!$adapter) {
            throw new \Exception('no payment adapter available');
        }

        if (!$adapter->canCheck()) {
            return;
        }

        $adapter->check($paymentIdentifier);
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
}
