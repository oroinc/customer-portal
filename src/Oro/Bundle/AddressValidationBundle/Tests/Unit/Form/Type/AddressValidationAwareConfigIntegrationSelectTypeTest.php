<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressValidationBundle\Form\Type\AddressValidationAwareConfigIntegrationSelectType;
use Oro\Bundle\AddressValidationBundle\Provider\AddressValidationSupportedChannelTypesProvider;
use Oro\Bundle\IntegrationBundle\Form\Type\ConfigIntegrationSelectType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AddressValidationAwareConfigIntegrationSelectTypeTest extends TestCase
{
    private AddressValidationAwareConfigIntegrationSelectType $formType;

    protected function setUp(): void
    {
        $this->formType = new AddressValidationAwareConfigIntegrationSelectType(
            new AddressValidationSupportedChannelTypesProvider(['foo', 'bar'])
        );
    }

    /**
     * @dataProvider getConfigureOptionsDataProvider
     */
    public function testConfigureOptions(array $options, array $expectedTypeNames): void
    {
        $resolver = new OptionsResolver();
        $this->formType->configureOptions($resolver);

        $resolved = $resolver->resolve($options);

        self::assertEquals($expectedTypeNames, $resolved['allowed_types']);
    }

    public function getConfigureOptionsDataProvider(): array
    {
        return [
            'no option' => [
                'options' => [],
                'expectedTypes' => ['foo', 'bar'],
            ],
            'null option' => [
                'options' => [
                    'allowed_types' => null,
                ],
                'expectedTypes' => ['foo', 'bar'],
            ],
            'empty array option' => [
                'options' => [
                    'allowed_types' => [],
                ],
                'expectedTypes' => ['foo', 'bar'],
            ],
            'non empty option' => [
                'options' => [
                    'allowed_types' => ['baz', 'bar'],
                ],
                'expectedTypes' => ['baz', 'bar', 'foo'],
            ],
        ];
    }

    public function testGetParent(): void
    {
        self::assertEquals(ConfigIntegrationSelectType::class, $this->formType->getParent());
    }
}
