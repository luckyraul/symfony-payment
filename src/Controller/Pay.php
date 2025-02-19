<?php

namespace Mygento\Payment\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Mygento\Payment\Repository\KeyRepository;
use Mygento\Payment\Repository\RegistrationRepository;
use Mygento\Payment\PaymentManager;
use Mygento\Payment\Config;
use Mygento\Payment\Entity\Key;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Pay
{
    public function __construct(
        private PaymentManager $manager,
        private KeyRepository $repo,
        private RegistrationRepository $regRepo,
        private Config $config,
    ) {}

    public function execute(string $order): RedirectResponse
    {
        $orderInfo = $this->decodeLink($order);
        if (!$orderInfo) {
            throw new NotFoundHttpException('The order does not exist');
        }
        $reg = $this->regRepo->findOneBy([
            'code' => $orderInfo->getCode(),
            'order' => $orderInfo->getOrder(),
        ]);
        if (!$reg || !$reg->getPaymentIdentifier()) {
            throw new NotFoundHttpException('The order does not exist');
        }
        $result = $this->manager->check($order, $reg->getPaymentIdentifier());
        if (0 !== bccomp($result->amountAuthorised, '0') || 0 !== bccomp($result->amountPaid, '0')) {
            return new RedirectResponse($this->config->getCallbackRedirect() ?? '/checkout/success', 302);
        }

        $reg = $this->regRepo->findOneBy([
            'code' => $orderInfo->getCode(),
            'order' => $orderInfo->getOrder(),
        ]);
        if (!$reg || !$reg->getPaymentIdentifier() || !$reg->getPaymentUrl()) {
            throw new NotFoundHttpException('The payment does not exist');
        }

        return new RedirectResponse($reg->getPaymentUrl(), 302);
    }

    private function decodeLink(string $hash): ?Key
    {
        $entity = $this->repo->findOneBy(['hkey' => $hash]);
        if (!$entity) {
            return null;
        }

        return $entity;
    }
}
