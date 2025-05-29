<?php

namespace Mygento\Payment\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Mygento\Payment\Repository\RegistrationRepository;
use Mygento\Payment\PaymentManager;
use Mygento\Payment\Config;
use Mygento\Payment\Event\PaymentCheck;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Check
{
    public function __construct(
        private PaymentManager $manager,
        private RegistrationRepository $repo,
        private Config $config,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(string $code, string $orderId): Response
    {
        $registration = $this->repo->findOneBy(['order' => $orderId, 'code' => $code]);
        if (!$registration || null === $registration->getPaymentIdentifier()) {
            throw new NotFoundHttpException('The order does not exist');
        }

        try {
            $info = $this->manager->check($registration->getCode(), $registration->getPaymentIdentifier());
            if ($registration->getPaymentIdentifier()) {
                $event = new PaymentCheck($registration->getPaymentIdentifier(), $info);
                $this->dispatcher->dispatch($event);
            }
        } catch (\Throwable) {
        }

        return new RedirectResponse($this->config->getCallbackRedirect() ?? '/checkout/success', 302);
    }
}
