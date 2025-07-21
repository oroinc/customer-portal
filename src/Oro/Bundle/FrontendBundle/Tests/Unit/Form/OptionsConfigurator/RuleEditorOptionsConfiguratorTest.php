<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\OptionsConfigurator;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorOptionsConfiguratorTest extends TestCase
{
    public function testConfigureOptionsNoRequired(): void
    {
        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "supportedNames" is missing.');

        $resolver->resolve([]);
    }

    /**
     * @dataProvider invalidOptionsDataProvider
     */
    public function testConfigureOptionsInvalid(array $options): void
    {
        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);

        $resolver->resolve($options);
    }

    public function invalidOptionsDataProvider(): array
    {
        return [
            'supportedNames type' => [['supportedNames' => true]],
            'allowedOperations type' => [['supportedNames' => [], 'allowedOperations' => false]],
            'dataSource type' => [['supportedNames' => [], 'dataSource' => 'source']],
            'pageComponent type' => [['supportedNames' => [], 'pageComponent' => []]],
        ];
    }

    public function testConfigureOptionsMinimal(): void
    {
        $options = [
            'supportedNames' => []
        ];

        $resolver = new OptionsResolver();
        $configurator = new RuleEditorOptionsConfigurator();
        $configurator->configureOptions($resolver);

        $expected = [
            'pageComponent' => 'oroform/js/app/components/expression-editor-component',
            'pageComponentOptions' => [],
            'supportedNames' => [],
            'dataSource' => [],
        ];
        $this->assertEquals($expected, $resolver->resolve($options));
    }

    public function testConfigureOptionsFull(): void
    {
        $options = [
            'supportedNames' => ['App\Entity\PriceList' => 'price_list'],
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
            'supportedNames' => ['App\Entity\PriceList' => 'price_list'],
            'allowedOperations' => ['math'],
            'dataProviderConfig' => [
                'optionsFilter' =>  ['exclude' => false, 'unidirectional' => false]
            ]
        ];
        $this->assertEquals($expected, $resolver->resolve($options));
    }
}
