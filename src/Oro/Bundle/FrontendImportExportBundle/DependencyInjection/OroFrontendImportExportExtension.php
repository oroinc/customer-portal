<?php

namespace Oro\Bundle\FrontendImportExportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroFrontendImportExportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('controllers.yml');
        $loader->load('block_types.yml');
        $loader->load('mq_topics.yml');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.yml');
        }
    }
}
