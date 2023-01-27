<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testProcessConfiguration(): void
    {
        $configuration = new Configuration();
        $processor     = new Processor();

        $expected = [
            'settings' => [
                'resolved' => true,
                Configuration::MAIN_NAVIGATION_MENU => [
                    'value' => 'commerce_main_menu',
                    'scope' => 'app'
                ],
            ]
        ];

        $this->assertEquals($expected, $processor->processConfiguration($configuration, []));
    }
}
