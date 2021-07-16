<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\OptionsConfigurator;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Bundle\ProductBundle\Expression\Autocomplete\AutocompleteFieldsProviderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorOptionsConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigureOptionsNoRequired()
    {
        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "entities" is missing.');

        $resolver->resolve([]);
    }

    /**
     * @dataProvider invalidOptionsDataProvider
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
            'entities type' => [['entities' => true]],
            'allowedOperations type' => [['entities' => [], 'allowedOperations' => false]],
            'dataSource type' => [['entities' => [], 'dataSource' => 'source']],
            'pageComponent type' => [['entities' => [], 'pageComponent' => []]],
        ];
    }

    public function testConfigureOptionsMinimal()
    {
        $options = [
            'entities' => []
        ];

        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $expected = [
            'pageComponent' => 'oroui/js/app/components/view-component',
            'pageComponentOptions' => [
                'view' => 'oroform/js/app/views/expression-editor-view',
            ],
            'dataSource' => [],
            'entities' => []
        ];
        $this->assertEquals($expected, $resolver->resolve($options));
    }

    public function testConfigureOptionsFull()
    {
        $options = [
            'entities' => [
                AutocompleteFieldsProviderInterface::ROOT_ENTITIES_KEY => [PriceList::class => 'price_list']
            ],
            'dataSource' => ['price_list' => 'test'],
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
            'entities' => [
                AutocompleteFieldsProviderInterface::ROOT_ENTITIES_KEY => [PriceList::class => 'price_list']
            ],
            'allowedOperations' => ['math']
        ];
        $this->assertEquals($expected, $resolver->resolve($options));
    }
}
