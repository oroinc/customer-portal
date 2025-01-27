<?php

namespace Oro\Bundle\AddressValidationBundle\Formatter;

use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter as LocaleAddressFormatter;
use Oro\Bundle\UIBundle\Tools\TextHighlighter;
use Twig\Environment as TwigEnvironment;

/**
 * Formats a {@see ResolvedAddress} taking into account the differences with the original address.
 */
class ResolvedAddressFormatter
{
    private string $highlightingTemplate = '<u class="highlight-text">%s</u>';

    public function __construct(
        private readonly LocaleAddressFormatter $addressFormatter,
        private readonly TextHighlighter $textHighlighter,
        private readonly TwigEnvironment $twigEnvironment
    ) {
    }

    public function setHighlightingTemplate(string $template): void
    {
        $this->highlightingTemplate = $template;
    }

    /**
     * Formats a {@see ResolvedAddress} according to the corresponding address format in the locale settings.
     * Takes into account the original address to highlight the differences by wrapping them
     * with the highlighting template.
     */
    public function formatResolvedAddress(
        ResolvedAddress $resolvedAddress,
        ?string $countryIso2 = null,
        ?string $newLineSeparator = "\n"
    ): string {
        if (!$countryIso2) {
            $countryIso2 = $this->addressFormatter->getCountry($resolvedAddress);
        }

        $addressFormat = $this->addressFormatter->getAddressFormat($countryIso2);
        $resolvedAddressParts = $this->getResolvedAddressParts($resolvedAddress, $addressFormat, $countryIso2);
        $formattedAddress = strtr(
            $addressFormat,
            array_combine(array_keys($resolvedAddressParts), array_column($resolvedAddressParts, 'value'))
        );
        $formattedAddress = str_replace(
            ['  ', " \n", '\n', $newLineSeparator . $newLineSeparator],
            [' ', "\n", $newLineSeparator, $newLineSeparator],
            $formattedAddress
        );

        return trim(trim($formattedAddress, $newLineSeparator));
    }

    /**
     * Provides the address parts according to the corresponding address format in the locale settings.
     * Takes into account the original address to highlight the differences by wrapping them
     * with the highlighting template.
     * Look into Resources/config/oro/address_format.yml for available formats.
     *
     * @return array<string,array{value: string, is_html_safe: bool}>
     *  [
     *      '%country%' => ['value' => 'US', 'is_html_safe' => true],
     *      '%region%' => ['value' => '<u>CA</u>', 'is_html_safe' => true],
     *  ]
     */
    public function getResolvedAddressParts(
        ResolvedAddress $resolvedAddress,
        string $addressFormat,
        ?string $countryIso2 = null
    ): array {
        if (!$countryIso2) {
            $countryIso2 = $this->addressFormatter->getCountry($resolvedAddress);
        }

        $resolvedAddressParts = $this->addressFormatter
            ->getAddressParts($resolvedAddress, $addressFormat, $countryIso2);
        $originalAddressParts = $this->addressFormatter
            ->getAddressParts($resolvedAddress->getOriginalAddress(), $addressFormat, $countryIso2);
        $highlightedAddressParts = [];

        foreach ($resolvedAddressParts as $key => $suggestedAddressPartValue) {
            $suggestedAddressPartValue = $this->escape($suggestedAddressPartValue);
            if ($suggestedAddressPartValue === '') {
                $highlightedAddressParts[$key] = ['value' => $suggestedAddressPartValue, 'is_html_safe' => true];
                continue;
            }

            $highlightedAddressParts[$key] = [
                'value' => $this->textHighlighter->highlightDifferences(
                    $suggestedAddressPartValue,
                    $this->escape($originalAddressParts[$key] ?? ''),
                    $this->highlightingTemplate
                ),
                'is_html_safe' => true,
            ];
        }

        return $highlightedAddressParts;
    }

    private function escape(string $string): string
    {
        return \twig_escape_filter($this->twigEnvironment, $string);
    }
}
