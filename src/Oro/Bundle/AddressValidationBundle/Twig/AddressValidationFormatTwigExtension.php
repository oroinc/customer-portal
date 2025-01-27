<?php

namespace Oro\Bundle\AddressValidationBundle\Twig;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Formatter\ResolvedAddressFormatter;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Oro\Bundle\LocaleBundle\Twig\FormattedAddressRenderer;
use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Provides the TWIG filters to format the {@see ResvoledAddress}:
 *   - oro_address_validation_format_resolved_address
 *   - oro_address_validation_format_resolved_address_html
 *   - oro_address_validation_format_original_address_html
 */
class AddressValidationFormatTwigExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    /**
     * @param ContainerInterface $container
     * @param array<string> $addressValidationFields
     */
    public function __construct(private ContainerInterface $container, private array $addressValidationFields)
    {
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            'oro_locale.formatter.address' => AddressFormatter::class,
            'oro_locale.twig.formatted_address_renderer' => FormattedAddressRenderer::class,
            'oro_address_validation.formatter.resolved_address' => ResolvedAddressFormatter::class,
            PropertyAccessorInterface::class,
        ];
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'oro_address_validation_format_resolved_address',
                $this->formatResolvedAddress(...),
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'oro_address_validation_format_resolved_address_html',
                $this->formatResolvedAddressHtml(...),
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'oro_address_validation_format_original_address_html',
                $this->formatOriginalAddressHtml(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function formatResolvedAddress(
        ?ResolvedAddress $resolvedAddress,
        ?string $country = null,
        string $newLineSeparator = "\n"
    ): string {
        if ($resolvedAddress === null) {
            return '';
        }

        return $this
            ->getResolvedAddressFormatter()
            ->formatResolvedAddress($resolvedAddress, $country, $newLineSeparator);
    }

    private function getResolvedAddressFormatter(): ResolvedAddressFormatter
    {
        return $this->container->get('oro_address_validation.formatter.resolved_address');
    }

    public function formatResolvedAddressHtml(
        ?ResolvedAddress $resolvedAddress,
        ?string $country = null,
        string $newLineSeparator = "\n"
    ): string {
        if ($resolvedAddress === null) {
            return '';
        }

        $addressFormatter = $this->getAddressFormatter();
        $country = $country ?: $addressFormatter->getCountry($resolvedAddress);
        $addressFormat = $addressFormatter->getAddressFormat($country);
        $resolvedAddressParts = $this->getResolvedAddressFormatter()
            ->getResolvedAddressParts($resolvedAddress, $addressFormat, $country);

        return $this
            ->getFormattedAddressRenderer()
            ->renderAddress($resolvedAddressParts, $addressFormat, $newLineSeparator);
    }

    public function formatOriginalAddressHtml(
        ?AbstractAddress $originalAddress,
        ?string $country = null,
        string $newLineSeparator = "\n"
    ): string {
        if ($originalAddress === null) {
            return '';
        }

        $propertyAccessor = $this->getPropertyAccessor();
        $preparedAddress = new (ClassUtils::getClass($originalAddress));
        foreach ($this->addressValidationFields as $fieldName) {
            $propertyAccessor->setValue(
                $preparedAddress,
                $fieldName,
                $propertyAccessor->getValue($originalAddress, $fieldName)
            );
        }

        $addressFormatter = $this->getAddressFormatter();
        $country = $country ?: $addressFormatter->getCountry($preparedAddress);
        $addressFormat = $addressFormatter->getAddressFormat($country);
        $addressParts = $this->getAddressFormatter()->getAddressParts($preparedAddress, $addressFormat, $country);

        return $this->getFormattedAddressRenderer()->renderAddress($addressParts, $addressFormat, $newLineSeparator);
    }

    private function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->container->get(PropertyAccessorInterface::class);
    }

    private function getAddressFormatter(): AddressFormatter
    {
        return $this->container->get('oro_locale.formatter.address');
    }

    private function getFormattedAddressRenderer(): FormattedAddressRenderer
    {
        return $this->container->get('oro_locale.twig.formatted_address_renderer');
    }
}
