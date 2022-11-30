<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection;

use Oro\Bundle\SecurityBundle\DependencyInjection\Extension\SecurityExtensionHelper;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroCustomerExtension extends Extension implements PrependExtensionInterface
{
    const ALIAS = 'oro_customer';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_api.yml');
        $loader->load('form_types.yml');
        $loader->load('ownership.yml');
        $loader->load('block_types.yml');
        $loader->load('form.yml');
        $loader->load('importexport.yml');
        $loader->load('controllers.yml');
        $loader->load('controllers_api.yml');
        $loader->load('commands.yml');
        $loader->load('mq_processors.yml');
        $loader->load('mq_topics.yml');

        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));
        $container->setParameter('oro_customer_user.login_sources', $config['login_sources']);

        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('services_test.yml');
        }

        $this->configureCustomerVisitorCookieFactory($container, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        /** @var ExtendedContainerBuilder $container */
        SecurityExtensionHelper::makeFirewallLatest($container, 'frontend_secure');
        SecurityExtensionHelper::makeFirewallLatest($container, 'frontend');
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    private function configureCustomerVisitorCookieFactory(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('oro_customer.authentication.customer_visitor_cookie_factory')
            ->replaceArgument(0, $config['visitor_session']['cookie_secure'])
            ->replaceArgument(1, $config['visitor_session']['cookie_httponly'])
            ->replaceArgument(3, $config['visitor_session']['cookie_samesite']);
    }
}
