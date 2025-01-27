<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\ResolvedAddress;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\ResolvedAddressAcceptor;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ResolvedAddressAcceptorTest extends TestCase
{
    private ResolvedAddressAcceptor $resolvedAddressAcceptor;

    protected function setUp(): void
    {
        $this->resolvedAddressAcceptor = new ResolvedAddressAcceptor(
            new PropertyAccessor(),
            ['country', 'region', 'city', 'street', 'street2', 'postalCode']
        );
    }

    public function testAcceptResolvedAddressWithDefaultFields(): void
    {
        $originalAddress = (new CustomerAddress())
            ->setCountry(new Country('US'))
            ->setRegion(new Region('CA'))
            ->setCity('Haines')
            ->setStreet('123 Main St')
            ->setStreet2('Apt 4')
            ->setPostalCode(94105);

        $resolvedAddress = (new ResolvedAddress($originalAddress))
            ->setCountry(new Country('US'))
            ->setRegion(new Region('CA'))
            ->setCity('Fresno')
            ->setStreet('456 Main St')
            ->setStreet2('Apt 5')
            ->setPostalCode(99999);

        $expectedAddress = (new CustomerAddress())
            ->setCountry(new Country('US'))
            ->setRegion(new Region('CA'))
            ->setCity('Fresno')
            ->setStreet('456 Main St')
            ->setStreet2('Apt 5')
            ->setPostalCode(99999);

        $acceptedAddress = $this->resolvedAddressAcceptor->acceptResolvedAddress($resolvedAddress);

        self::assertEquals($expectedAddress, $acceptedAddress);
    }

    public function testAcceptResolvedAddressWithCustomFields(): void
    {
        $originalAddress = (new CustomerAddress())
            ->setLabel('Original Address')
            ->setCountry(new Country('US'))
            ->setRegion(new Region('CA'))
            ->setCity('Haines')
            ->setStreet('123 Main St')
            ->setStreet2('Apt 4')
            ->setPostalCode(94105);

        $resolvedAddress = (new ResolvedAddress($originalAddress))
            ->setLabel('Resolved Address')
            ->setCountry(new Country('US'))
            ->setRegion(new Region('CA'))
            ->setCity('Fresno')
            ->setStreet('456 Main St')
            ->setStreet2('Apt 5')
            ->setPostalCode(99999);

        $expectedAddress = (new CustomerAddress())
            ->setLabel('Resolved Address')
            ->setCountry(new Country('US'))
            ->setRegion(new Region('CA'))
            ->setCity('Haines')
            ->setStreet('456 Main St')
            ->setStreet2('Apt 5')
            ->setPostalCode(99999);

        $resolvedAddressAcceptor = new ResolvedAddressAcceptor(
            new PropertyAccessor(),
            ['label', 'street', 'street2', 'postalCode']
        );
        $acceptedAddress = $resolvedAddressAcceptor->acceptResolvedAddress($resolvedAddress);

        self::assertEquals($expectedAddress, $acceptedAddress);
    }
}
