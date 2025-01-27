<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Model;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressValidationBundle\Model\AddressValidationModel;
use PHPUnit\Framework\TestCase;

final class AddressValidationModelTest extends TestCase
{
    private AddressValidationModel $addressValidation;

    private Address $address;

    protected function setUp(): void
    {
        $this->address = new Address();
        $this->addressValidation = new AddressValidationModel($this->address);
    }

    /**
     * @dataProvider getSuggestionDataProvider
     */
    public function testThatSuggestedAddressIsSelected(?string $type): void
    {
        $this->addressValidation->setSuggestionType($type);
        self::assertTrue($this->addressValidation->isSuggestedAddressSelected());
    }

    public function testThatSelectedAddressReturned(): void
    {
        self::assertEquals(new Address(), $this->addressValidation->getEnteredAddress());
    }

    public function testThatSuggestionAddressReturned(): void
    {
        $this->addressValidation->setSuggestedAddress(new Address());
        self::assertEquals(new Address(), $this->addressValidation->getSuggestedAddress());
    }

    public static function getSuggestionDataProvider(): array
    {
        return [
            'empty type' => [null],
            'suggested' => ['suggested'],
        ];
    }
}
