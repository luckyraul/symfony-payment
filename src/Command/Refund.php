<?php

namespace Mygento\Payment\Command;

use Mygento\Payment\PaymentManager;
use Mygento\Payment\Repository\RegistrationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('payment:registration:refund', 'Refund payment registration by ID')]
class Refund extends Command
{
    public function __construct(
        private PaymentManager $manager,
        private RegistrationRepository $repo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Invoice ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $entity = $this->repo->find($id);
        if (!$entity) {
            $output->writeln('Refund ID: ' . $id . ' not found');

            return self::INVALID;
        }
        if (null === $entity->getPaymentIdentifier()) {
            $output->writeln('Refund ID: ' . $id . ' is reseted');

            return self::INVALID;
        }
        $this->manager->refund($entity->getCode(), $entity->getPaymentIdentifier());
        $output->writeln('Refund ID: ' . $id . ' Done');

        return self::SUCCESS;
    }
}
