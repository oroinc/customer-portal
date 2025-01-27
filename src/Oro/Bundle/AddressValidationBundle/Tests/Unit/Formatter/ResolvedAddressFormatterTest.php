<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Formatter;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Formatter\ResolvedAddressFormatter;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter as LocaleAddressFormatter;
use Oro\Bundle\UIBundle\Tools\TextHighlighter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader;

class ResolvedAddressFormatterTest extends TestCase
{
    private LocaleAddressFormatter&MockObject $addressFormatter;

    private TextHighlighter&MockObject $textHighlighter;

    private ResolvedAddressFormatter $formatter;

    protected function setUp(): void
    {
        $this->addressFormatter = $this->createMock(LocaleAddressFormatter::class);
        $this->textHighlighter = $this->createMock(TextHighlighter::class);

        $this->formatter = new ResolvedAddressFormatter(
            $this->addressFormatter,
            $this->textHighlighter,
            new TwigEnvironment(new ArrayLoader())
        );
    }

    public function testFormatResolvedAddress(): void
    {
        $originalAddress = $this->createMock(AbstractAddress::class);
        $resolvedAddress = new ResolvedAddress($originalAddress);

        $countryIso2 = 'US';
        $this->addressFormatter->expects(self::once())
            ->method('getCountry')
            ->with($resolvedAddress)
            ->willReturn($countryIso2);

        $addressFormat = "%name%\n%street%\n%city%, %region% %postal_code%\n%country%";
        $this->addressFormatter
            ->expects(self::once())
            ->method('getAddressFormat')
            ->with($countryIso2)
            ->willReturn($addressFormat);

        $resolvedAddressParts = [
            '%name%' => '',
            '%street%' => '123 Elm St',
            '%city%' => 'Los Angeles',
            '%region%' => 'CA',
            '%postal_code%' => '90001',
            '%country%' => 'US',
        ];

        $originalAddressParts = [
            '%name%' => '',
            '%street%' => '123 Elm Street',
            '%city%' => 'San Francisco',
            '%region%' => 'CA',
            '%postal_code%' => '90001',
            '%country%' => 'US',
        ];

        $this->addressFormatter
            ->method('getAddressParts')
            ->withConsecutive(
                [$resolvedAddress, $addressFormat, $countryIso2],
                [$originalAddress, $addressFormat, $countryIso2]
            )
            ->willReturnOnConsecutiveCalls($resolvedAddressParts, $originalAddressParts);

        $this->textHighlighter
            ->method('highlightDifferences')
            ->withConsecutive(
                ['123 Elm St', '123 Elm Street', '<u class="highlight-text">%s</u>'],
                ['Los Angeles', 'San Francisco', '<u class="highlight-text">%s</u>'],
                ['CA', 'CA', '<u class="highlight-text">%s</u>'],
                ['90001', '90001', '<u class="highlight-text">%s</u>'],
                ['US', 'US', '<u class="highlight-text">%s</u>'],
            )
            ->willReturnOnConsecutiveCalls(
                '123 Elm <u class="highlight-text">St</u>',
                '<u class="highlight-text">Los</u>' .
                ' <u class="highlight-text">Angeles</u>',
                'CA',
                '90001',
                'US'
            );

        $result = $this->formatter->formatResolvedAddress($resolvedAddress);

        self::assertSame(
            '123 Elm <u class="highlight-text">St</u>' . PHP_EOL .
            '<u class="highlight-text">Los</u> ' .
            '<u class="highlight-text">Angeles</u>, CA 90001' . PHP_EOL . 'US',
            $result
        );
    }

    public function testGetResolvedAddressParts(): void
    {
        $originalAddress = $this->createMock(AbstractAddress::class);
        $resolvedAddress = new ResolvedAddress($originalAddress);

        $addressFormat = '%country% %region%';
        $countryIso2 = 'US';
        $resolvedAddressParts = [
            '%country%' => 'US',
            '%region%' => 'CA',
        ];
        $originalAddressParts = [
            '%country%' => 'US',
            '%region%' => 'NY',
        ];

        $this->addressFormatter->expects(self::once())
            ->method('getCountry')
            ->with($resolvedAddress)
            ->willReturn($countryIso2);

        $this->addressFormatter->expects(self::exactly(2))
            ->method('getAddressParts')
            ->withConsecutive(
                [$resolvedAddress, $addressFormat, $countryIso2],
                [$originalAddress, $addressFormat, $countryIso2]
            )
            ->willReturnOnConsecutiveCalls($resolvedAddressParts, $originalAddressParts);

        $this->textHighlighter->expects(self::exactly(2))
            ->method('highlightDifferences')
            ->withConsecutive(
                ['US', 'US', '<u class="highlight-text">%s</u>'],
                ['CA', 'NY', '<u class="highlight-text">%s</u>']
            )
            ->willReturnOnConsecutiveCalls('US', '<u class="highlight-text">CA</u>');

        $result = $this->formatter->getResolvedAddressParts($resolvedAddress, $addressFormat);

        self::assertSame(
            [
                '%country%' => ['value' => 'US', 'is_html_safe' => true],
                '%region%' => [
                    'value' => '<u class="highlight-text">CA</u>',
                    'is_html_safe' => true,
                ],
            ],
            $result
        );
    }

    public function testGetResolvedAddressPartsWithEmptyResolvedAddressPart(): void
    {
        $originalAddress = $this->createMock(AbstractAddress::class);
        $resolvedAddress = new ResolvedAddress($originalAddress);
        $addressFormat = '%street%';
        $countryIso2 = 'US';
        $resolvedAddressParts = [
            '%street%' => '',
        ];

        $this->addressFormatter->expects(self::once())
            ->method('getCountry')
            ->with($resolvedAddress)
            ->willReturn($countryIso2);

        $this->addressFormatter->expects(self::exactly(2))
            ->method('getAddressParts')
            ->withConsecutive(
                [$resolvedAddress, $addressFormat, $countryIso2],
                [$originalAddress, $addressFormat, $countryIso2]
            )
            ->willReturnOnConsecutiveCalls($resolvedAddressParts, []);

        $result = $this->formatter->getResolvedAddressParts($resolvedAddress, $addressFormat);

        self::assertSame(
            [
                '%street%' => ['value' => '', 'is_html_safe' => true],
            ],
            $result
        );
    }

    public function testGetResolvedAddressPartsWithoutCountryIso2(): void
    {
        $originalAddress = $this->createMock(AbstractAddress::class);
        $resolvedAddress = new ResolvedAddress($originalAddress);
        $addressFormat = '%city%';
        $resolvedAddressParts = [
            '%city%' => 'Los Angeles',
        ];
        $originalAddressParts = [
            '%city%' => 'San Francisco',
        ];

        $this->addressFormatter->expects(self::once())
            ->method('getCountry')
            ->with($resolvedAddress)
            ->willReturn('US');

        $this->addressFormatter->expects(self::exactly(2))
            ->method('getAddressParts')
            ->withConsecutive(
                [$resolvedAddress, $addressFormat, 'US'],
                [$originalAddress, $addressFormat, 'US']
            )
            ->willReturnOnConsecutiveCalls($resolvedAddressParts, $originalAddressParts);

        $this->textHighlighter->expects(self::once())
            ->method('highlightDifferences')
            ->with('Los Angeles', 'San Francisco', '<u class="highlight-text">%s</u>')
            ->willReturn('<u class="highlight-text">Los Angeles</u>');

        $result = $this->formatter->getResolvedAddressParts($resolvedAddress, $addressFormat);

        self::assertSame(
            [
                '%city%' => [
                    'value' => '<u class="highlight-text">Los Angeles</u>',
                    'is_html_safe' => true,
                ],
            ],
            $result
        );
    }
}
