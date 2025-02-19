<?php

namespace Mygento\Payment\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Mygento\Payment\Repository\RegistrationRepository;
use Mygento\Payment\PaymentManager;
use Mygento\Payment\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Check
{
    public function __construct(
        private PaymentManager $manager,
        private RegistrationRepository $repo,
        private Config $config,
        // private UrlGeneratorInterface $router,
    ) {}

    public function execute(string $code, string $orderId): Response
    {
        $registration = $this->repo->findOneBy(['order' => $orderId, 'code' => $code]);
        if (!$registration || null === $registration->getPaymentIdentifier()) {
            throw new NotFoundHttpException('The order does not exist');
        }

        try {
            $this->manager->check($registration->getCode(), $registration->getPaymentIdentifier());
        } catch (\Throwable) {
        }

        return new RedirectResponse($this->config->getCallbackRedirect() ?? '/checkout/success', 302);
    }
}
