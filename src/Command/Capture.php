<?php

namespace Mygento\Payment\Command;

use Mygento\Payment\Management;
use Mygento\Payment\Repository\RegistrationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('payment:registration:capture', 'Capture payment registration by ID')]
class Capture extends Command
{
    public function __construct(
        private Management $manager,
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
            $output->writeln('Capture ID: ' . $id . ' not found');

            return self::INVALID;
        }
        if (null === $entity->getPaymentIdentifier()) {
            $output->writeln('Capture ID: ' . $id . ' is reseted');

            return self::INVALID;
        }

        $this->manager->capture($entity->getCode(), $entity->getPaymentIdentifier());
        $output->writeln('Capture ID: ' . $id . ' Done');

        return self::SUCCESS;
    }
}
