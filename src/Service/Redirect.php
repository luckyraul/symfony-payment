<?php

namespace Mygento\Payment\Service;

use Mygento\Payment\Repository\KeyRepository;
use Mygento\Payment\Entity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Redirect
{
    public function __construct(
        private KeyRepository $keyRepo,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function findRedirectKey(string $code, string $orderId): ?string
    {
        $entity = $this->keyRepo->findOneBy(['code' => $code, 'order' => $orderId]);

        return $entity ? $this->urlGenerator->generate(
            'mygento_payment_pay',
            ['order' => $entity->getHkey()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        ) : null;
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
}
