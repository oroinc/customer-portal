<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer\AbstractAddressNormalizer;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractCustomerAddressNormalizerTest extends WebTestCase
{
    protected AbstractAddressNormalizer $normalizer;

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupportsNormalization(string|object $data, bool $expected)
    {
        if (is_string($data)) {
            $data = $this->getReference($data);
        }
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data));
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupportsDenormalization(string|object $data, bool $expected)
    {
        if (is_string($data)) {
            $data = $this->getReference($data);
        }
        $this->assertSame($expected, $this->normalizer->supportsDenormalization([], get_class($data)));
    }

    abstract public function supportsDataProvider();

    protected static function getColumnsForNormalizer(): array
    {
        return [
            'city' => 'Rochester',
            'firstName' => 'First',
            'lastName' => 'Last',
            'middleName' => 'Middle',
            'namePrefix' => 'Mr',
            'nameSuffix' => 'Sf',
            'organization' => 'Test Org',
            'phone' => '+123321123',
            'postalCode' => '14608',
            'primary' => true,
            'regionText' => null,
            'street' => '1215 Caldwell Road',
            'street2' => 'street2',
            'country' => [
                'iso2Code' => 'US'
            ],
            'owner' => [
                'username' => 'admin'
            ],
            'region' => [
                'combinedCode' => 'US-NY'
            ],
        ];
    }
}
