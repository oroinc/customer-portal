<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\OptionsConfigurator;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Bundle\ProductBundle\Expression\Autocomplete\AutocompleteFieldsProviderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorOptionsConfiguratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigureOptionsNoRequired()
    {
        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "rootEntities" is missing.');

        $resolver->resolve([]);
    }

    /**
     * @dataProvider invalidOptionsDataProvider
     * @param array $options
     */
    public function testConfigureOptionsInvalid(array $options)
    {
        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);

        $resolver->resolve($options);
    }

    /**
     * @return array
     */
    public function invalidOptionsDataProvider()
    {
        return [
            'rootEntities type' => [['rootEntities' => true]],
            'allowedOperations type' => [['rootEntities' => [], 'allowedOperations' => false]],
            'dataSource type' => [['rootEntities' => [], 'dataSource' => 'source']],
            'pageComponent type' => [['rootEntities' => [], 'pageComponent' => []]],
        ];
    }

    public function testConfigureOptionsMinimal()
    {
        $options = [
            'rootEntities' => []
        ];

        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $expected = [
            'pageComponent' => 'oroform/js/app/components/expression-editor-component',
            'pageComponentOptions' => [],
            'rootEntities' => [],
            'dataSource' => [],
        ];
        $this->assertEquals($expected, $resolver->resolve($options));
    }

    public function testConfigureOptionsFull()
    {
        $options = [
            'rootEntities' => ['price_list'],
            'dataSource' => ['price_list' => 'test'],
            'dataProviderConfig' => [
                'optionsFilter' =>  ['exclude' => false, 'unidirectional' => false]
            ],
            'pageComponent' => 'custom-component',
            'pageComponentOptions' => [
                'view' => 'custom-view',
            ],
            'allowedOperations' => ['math']
        ];

        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $expected = [
            'pageComponent' => 'custom-component',
            'pageComponentOptions' => [
                'view' => 'custom-view',
            ],
            'dataSource' => ['price_list' => 'test'],
            'rootEntities' => ['price_list'],
            'allowedOperations' => ['math'],
            'dataProviderConfig' => [
                'optionsFilter' =>  ['exclude' => false, 'unidirectional' => false]
            ]
        ];
        $this->assertEquals($expected, $resolver->resolve($options));
    }
}
