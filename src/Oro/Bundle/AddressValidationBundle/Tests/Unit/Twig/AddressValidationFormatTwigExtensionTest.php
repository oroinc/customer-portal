<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Twig;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Formatter\ResolvedAddressFormatter;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\AddressValidationBundle\Tests\Unit\Stub\AddressValidatedAtAwareStub;
use Oro\Bundle\AddressValidationBundle\Twig\AddressValidationFormatTwigExtension;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Oro\Bundle\LocaleBundle\Twig\FormattedAddressRenderer;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AddressValidationFormatTwigExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private AddressFormatter&MockObject $addressFormatter;

    private FormattedAddressRenderer&MockObject $formattedAddressRenderer;

    private ResolvedAddressFormatter&MockObject $resolvedAddressFormatter;

    private AddressValidationFormatTwigExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->addressFormatter = $this->createMock(AddressFormatter::class);
        $this->formattedAddressRenderer = $this->createMock(FormattedAddressRenderer::class);
        $this->resolvedAddressFormatter = $this->createMock(ResolvedAddressFormatter::class);
        $propertyAccessor = new PropertyAccessor();

        $container = self::getContainerBuilder()
            ->add('oro_locale.formatter.address', $this->addressFormatter)
            ->add('oro_locale.twig.formatted_address_renderer', $this->formattedAddressRenderer)
            ->add('oro_address_validation.formatter.resolved_address', $this->resolvedAddressFormatter)
            ->add(PropertyAccessorInterface::class, $propertyAccessor)
            ->getContainer($this);

        $this->extension = new AddressValidationFormatTwigExtension($container, ['city', 'street']);
    }

    public function testFormatResolvedAddress(): void
    {
        $address = new ResolvedAddress($this->createMock(AbstractAddress::class));
        $country = 'CA';
        $newLineSeparator = '<br/>';
        $expectedResult = 'expected result';

        $this->resolvedAddressFormatter
            ->expects(self::once())
            ->method('formatResolvedAddress')
            ->with($address, $country, $newLineSeparator)
            ->willReturn($expectedResult);

        self::assertEquals(
            $expectedResult,
            self::callTwigFilter(
                $this->extension,
                'oro_address_validation_format_resolved_address',
                [$address, $country, $newLineSeparator]
            )
        );
    }

    public function testFormatResolvedAddressHtmlWithCountry(): void
    {
        $address = new ResolvedAddress($this->createMock(AbstractAddress::class));
        $resolvedAddressParts = ['%part1%' => 'value1', '%part2%' => 'value2'];
        $addressFormat = '%part1%\n%part2%';
        $newLineSeparator = "\n";
        $country = 'US';

        $this->addressFormatter->expects(self::never())
            ->method('getCountry');

        $this->addressFormatter->expects(self::once())
            ->method('getAddressFormat')
            ->with($country)
            ->willReturn($addressFormat);

        $this->resolvedAddressFormatter->expects(self::once())
            ->method('getResolvedAddressParts')
            ->with($address, $addressFormat, $country)
            ->willReturn($resolvedAddressParts);

        $expectedResult = 'rendered resolved address';
        $this->formattedAddressRenderer
            ->expects(self::once())
            ->method('renderAddress')
            ->with($resolvedAddressParts, $addressFormat, $newLineSeparator)
            ->willReturn($expectedResult);

        self::assertEquals(
            $expectedResult,
            self::callTwigFilter(
                $this->extension,
                'oro_address_validation_format_resolved_address_html',
                [$address, $country, $newLineSeparator]
            )
        );
    }

    public function testFormatResolvedAddressHtmlWithoutCountry(): void
    {
        $resolvedAddressParts = ['%part1%' => 'value1', '%part2%' => 'value2'];
        $addressFormat = '%part1%\n%part2%';
        $newLineSeparator = "\n";
        $country = 'US';
        $address = new ResolvedAddress($this->createMock(AbstractAddress::class));

        $this->addressFormatter->expects(self::once())
            ->method('getCountry')
            ->willReturn($country);

        $this->addressFormatter->expects(self::once())
            ->method('getAddressFormat')
            ->with($country)
            ->willReturn($addressFormat);

        $this->resolvedAddressFormatter->expects(self::once())
            ->method('getResolvedAddressParts')
            ->with($address, $addressFormat, $country)
            ->willReturn($resolvedAddressParts);

        $expectedResult = 'rendered resolved address';
        $this->formattedAddressRenderer
            ->expects(self::once())
            ->method('renderAddress')
            ->with($resolvedAddressParts, $addressFormat, $newLineSeparator)
            ->willReturn($expectedResult);

        self::assertEquals(
            $expectedResult,
            self::callTwigFilter(
                $this->extension,
                'oro_address_validation_format_resolved_address_html',
                [$address, null, $newLineSeparator]
            )
        );
    }

    public function testFormatOriginalAddressHtmlWithCountry(): void
    {
        $addressParts = ['%part1%' => 'value1', '%part2%' => 'value2'];
        $addressFormat = '%part1%\n%part2%';
        $newLineSeparator = "\n";
        $country = 'US';
        $address = (new AddressValidatedAtAwareStub())
            ->setLabel('original address')
            ->setCity('sample city')
            ->setStreet('sample street');

        $preparedAddress = (new AddressValidatedAtAwareStub())
            ->setCity($address->getCity())
            ->setStreet($address->getStreet());

        $this->addressFormatter->expects(self::never())
            ->method('getCountry');

        $this->addressFormatter->expects(self::once())
            ->method('getAddressFormat')
            ->with($country)
            ->willReturn($addressFormat);

        $this->addressFormatter->expects(self::once())
            ->method('getAddressParts')
            ->with($preparedAddress, $addressFormat, $country)
            ->willReturn($addressParts);

        $expectedResult = 'rendered resolved address';
        $this->formattedAddressRenderer
            ->expects(self::once())
            ->method('renderAddress')
            ->with($addressParts, $addressFormat, $newLineSeparator)
            ->willReturn($expectedResult);

        self::assertEquals(
            $expectedResult,
            self::callTwigFilter(
                $this->extension,
                'oro_address_validation_format_original_address_html',
                [$address, $country, $newLineSeparator]
            )
        );
    }
}
