<?php

namespace Mygento\Payment\Service;

use Mygento\Payment\Repository\TransactionRepository;
use Mygento\Payment\Entity;
use Mygento\Payment\Model\TransactionSummary;

class Transaction
{
    public function __construct(private TransactionRepository $transRepo) {}

    public function getSummary(string $code, string $order): TransactionSummary
    {
        $data = $this->transRepo->getTransactionSummaryByOrder($code, $order);
        $result = [
            Entity\Transaction::AUTH_TYPE => 0,
            Entity\Transaction::CAPTURE_TYPE => 0,
            Entity\Transaction::VOID_TYPE => 0,
            Entity\Transaction::REFUND_TYPE => 0,
        ];
        $types = array_flip(Entity\Transaction::TRANSACTION_TYPE);

        foreach ($data as $tr) {
            $result[$types[$tr['transactionType']]] += $tr['sum'];
        }

        return new TransactionSummary(
            (string) $result[Entity\Transaction::AUTH_TYPE],
            (string) $result[Entity\Transaction::CAPTURE_TYPE],
            (string) $result[Entity\Transaction::VOID_TYPE],
            (string) $result[Entity\Transaction::REFUND_TYPE],
        );
    }

    public function findAuthTransaction(string $code, string $paymentIdentifier): ?Entity\Transaction
    {
        return $this->transRepo->findOneBy(
            [
                'code' => $code,
                'transactionType' => Entity\Transaction::AUTH,
                'paymentIdentifier' => $paymentIdentifier,
            ],
        );
    }

    public function findCaptureTransaction(string $code, string $paymentIdentifier): ?Entity\Transaction
    {
        return $this->transRepo->findOneBy(
            [
                'code' => $code,
                'transactionType' => Entity\Transaction::CAPTURE,
                'paymentIdentifier' => $paymentIdentifier,
            ],
        );
    }

    /**
     * @param mixed[] $rawData
     */
    public function createTransaction(
        string $code,
        string $orderId,
        string $paymentIdentifier,
        string $transactionId,
        int $transactionType,
        string $amount,
        string $currency,
        ?Entity\Transaction $parent = null,
        ?array $rawData = null,
        ?string $savedPaymentIdentity = null,
    ): void {
        $entity = new Entity\Transaction($code, $orderId, $paymentIdentifier, $transactionId, $transactionType, $amount, $currency);
        if (null !== $parent) {
            $entity->setParentTransaction($parent);
        }
        if (null !== $rawData) {
            $entity->setRawData($rawData);
        }
        if (null !== $savedPaymentIdentity) {
            $entity->setSavedPaymentIdentity($savedPaymentIdentity);
        }
        $this->transRepo->save($entity, true);
    }
}
