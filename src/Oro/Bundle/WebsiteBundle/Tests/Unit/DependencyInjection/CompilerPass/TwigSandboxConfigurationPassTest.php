<?php

namespace Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TwigSandboxConfigurationPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $securityPolicyDef = $container->register('oro_email.twig.email_security_policy')
            ->setArguments([[], ['some_existing_filter'], [], [], ['some_existing_function']]);
        $rendererDef = $container->register('oro_email.email_renderer');

        $compiler = new TwigSandboxConfigurationPass();
        $compiler->process($container);

        self::assertEquals(
            [
                [],
                [
                    'some_existing_filter',
                    'oro_format_datetime_by_entity',
                    'oro_format_date_by_entity',
                    'oro_format_day_by_entity',
                    'oro_format_time_by_entity'
                ],
                [],
                [],
                ['some_existing_function', 'website_path', 'website_secure_path']
            ],
            $securityPolicyDef->getArguments()
        );
        self::assertEquals(
            [
                ['addExtension', [new Reference('oro_website.twig.website_path')]],
                ['addExtension', [new Reference('oro_website.twig.entity_date_time_extension')]]
            ],
            $rendererDef->getMethodCalls()
        );
    }
}
