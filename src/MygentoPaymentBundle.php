<?php

namespace Mygento\Payment;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class MygentoPaymentBundle extends AbstractBundle
{
    public const VERSION = '1.0.0';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    /**
     * @param mixed[] $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()->set('mygento.payment_config', Config::class)
            ->arg(0, $config['config']['return_url'] ?? null)
            ->alias(Config::class, 'mygento.payment_config');

        $container->services()->set('mygento.payment_keyrepo', Repository\KeyRepository::class)
            ->args([service('doctrine')])
            ->alias(Repository\KeyRepository::class, 'mygento.payment_keyrepo');
        $container->services()->set('mygento.payment_transrepo', Repository\TransactionRepository::class)
            ->args([service('doctrine')])
            ->alias(Repository\TransactionRepository::class, 'mygento.payment_transrepo');
        $container->services()->set('mygento.payment_regrepo', Repository\RegistrationRepository::class)
            ->args([service('doctrine')])
            ->alias(Repository\RegistrationRepository::class, 'mygento.payment_regrepo');
        $container->services()->set('mygento.payment_service', Service\Management::class)
            ->arg(0, service('mygento.payment_regrepo'))
            ->arg(1, service('mygento.payment_transrepo'))
            ->arg(2, service('mygento.payment_keyrepo'))
            ->arg(3, service('router'))
            ->alias(Service\Management::class, 'mygento.payment_service');
        $container->services()->set('mygento.payment_manager', PaymentManager::class)
            ->args([
                service('mygento.payment_transrepo'),
                service('mygento.payment_regrepo'),
                service('mygento.payment_service'),
                tagged_iterator('mygento.payment_adapter_factory', null, 'getCode'),
            ])
            ->alias(PaymentManager::class, 'mygento.payment_manager');
        $container->services()
            ->load('Mygento\\Payment\\Controller\\', './Controller/*.php')
            ->autowire()
            ->tag('controller.service_arguments');
        $container->services()
            ->load('Mygento\\Payment\\Command\\', './Command/*.php')
            ->autowire()
            ->tag('console.command');
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->arrayNode('config')
            ->children()
            ->scalarNode('return_url')->end()
            ->end()
            ->end()
            ->end();
    }
}
