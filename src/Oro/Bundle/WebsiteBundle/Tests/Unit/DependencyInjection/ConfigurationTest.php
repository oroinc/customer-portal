<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\WebsiteBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf(TreeBuilder::class, $builder);
    }

    public function testProcessConfiguration()
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $expected = [
            'settings' => [
                'resolved' => true,
                Configuration::URL => [
                    'value' => '',
                    'scope' => 'app',
                ],
                Configuration::SECURE_URL => [
                    'value' => '',
                    'scope' => 'app',
                ],
            ],
        ];
        $this->assertEquals($expected, $processor->processConfiguration($configuration, []));
    }
}
