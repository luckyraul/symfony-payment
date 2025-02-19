<?php

namespace Mygento\Payment\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Mygento\Payment\Repository\RegistrationRepository;
use Mygento\Payment\Repository\TransactionRepository;
use Mygento\Payment\Repository\KeyRepository;
use Mygento\Payment\Entity;
use Mygento\Payment\Model\TransactionSummary;

class Management
{
    public function __construct(
        private RegistrationRepository $regRepo,
        private TransactionRepository $transRepo,
        private KeyRepository $keyRepo,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function getCheckUrl(string $code, string $orderId): string
    {
        return $this->urlGenerator->generate(
            'mygento_payment_check',
            ['code' => $code, 'orderId' => $orderId],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    public function createRedirectKey(
        string $code,
        string $orderId,
    ): string {
        $hash = hash('sha1', base64_encode(microtime() . $orderId . rand(1, 1048576)));
        $entity = new Entity\Key($code, $orderId, $hash);

        $this->keyRepo->save($entity, true);

        return $this->urlGenerator->generate(
            'mygento_payment_pay',
            ['order' => $entity->getHkey()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    public function findRedirectKey(string $code, string $orderId): ?string
    {
        $entity = $this->keyRepo->findOneBy(['code' => $code, 'order' => $orderId]);

        return $entity ? $this->urlGenerator->generate(
            'mygento_payment_pay',
            ['order' => $entity->getHkey()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        ) : null;
    }

    public function createRegistration(
        string $code,
        string $orderId,
        string $paymentIdentifier,
        string $paymentUrl,
        int $paymentType,
    ): Entity\Registration {
        $model = new Entity\Registration($code, $orderId, $paymentIdentifier, $paymentUrl, $paymentType);
        $this->regRepo->save($model, true);

        return $model;
    }

    public function findRegistration(string $code, string $orderId): ?Entity\Registration
    {
        return $this->regRepo->findOneBy(['code' => $code, 'order' => $orderId]);
    }

    public function getTransactionSummary(string $code, string $paymentIdentifier): TransactionSummary
    {
        $data = $this->transRepo->getTransactionSummary($code, $paymentIdentifier);
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

    public function resetRegistration(string $code, string $orderId): void
    {
        $entity = $this->findRegistration($code, $orderId);
        if ($entity) {
            $entity->setPaymentIdentifier(null);
            $entity->setPaymentUrl(null);
            $entity->setTry($entity->getTry() + 1);
            $this->regRepo->save($entity, true);
        }
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
