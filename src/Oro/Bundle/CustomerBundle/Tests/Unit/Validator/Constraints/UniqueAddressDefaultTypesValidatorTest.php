<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueAddressDefaultTypes;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueAddressDefaultTypesValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueAddressDefaultTypesValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new UniqueAddressDefaultTypesValidator();
    }

    public function testValidateExceptionWhenInvalidArgumentType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "array or Traversable and ArrayAccess", "bool" given'
        );

        $constraint = new UniqueAddressDefaultTypes();
        $this->validator->validate(false, $constraint);
    }

    public function testValidateExceptionWhenInvalidArgumentElementType(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'type "Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress", "array" given'
        );

        $constraint = new UniqueAddressDefaultTypes();
        $this->validator->validate([1], $constraint);
    }

    /**
     * @dataProvider validAddressesDataProvider
     */
    public function testValidateValid(array $addresses): void
    {
        $constraint = $this->createMock(UniqueAddressDefaultTypes::class);
        $this->validator->validate($addresses, $constraint);

        $this->assertNoViolation();
    }

    public function validAddressesDataProvider(): array
    {
        return [
            'no addresses' => [
                [],
            ],
            'one address without type' => [
                [$this->getDefaultTypedAddress([])],
            ],
            'one address with type' => [
                [$this->getDefaultTypedAddress(['billing' => 'billing label'])],
            ],
            'many addresses unique types' => [
                [
                    $this->getDefaultTypedAddress(['billing' => 'billing label']),
                    $this->getDefaultTypedAddress(['shipping' => 'shipping label']),
                    $this->getDefaultTypedAddress(['billing_corporate' => 'billing_corporate label']),
                    $this->getDefaultTypedAddress([]),
                ],
            ],
            'empty address' => [
                [
                    $this->getDefaultTypedAddress(['billing' => 'billing label']),
                    $this->getDefaultTypedAddress(['shipping' => 'shipping label']),
                    $this->getDefaultTypedAddress([], true),
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidAddressesDataProvider
     */
    public function testValidateInvalid(array $addresses, string $types): void
    {
        $constraint = $this->createMock(UniqueAddressDefaultTypes::class);
        $this->validator->validate($addresses, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ types }}', $types)
            ->assertRaised();
    }

    public function invalidAddressesDataProvider(): array
    {
        return [
            'several addresses with one same type' => [
                [
                    $this->getDefaultTypedAddress(['billing' => 'billing label']),
                    $this->getDefaultTypedAddress(['billing' => 'billing label', 'shipping' => 'shipping label']),
                ],
                '"billing label"',
            ],
            'several addresses with two same types' => [
                [
                    $this->getDefaultTypedAddress(['billing' => 'billing label']),
                    $this->getDefaultTypedAddress(['shipping' => 'shipping label']),
                    $this->getDefaultTypedAddress(['billing' => 'billing label', 'shipping' => 'shipping label']),
                ],
                '"billing label", "shipping label"',
            ],
        ];
    }

    private function getDefaultTypedAddress(array $addressTypes, bool $isEmpty = false): AbstractDefaultTypedAddress
    {
        $addressTypeEntities = [];
        foreach ($addressTypes as $name => $label) {
            $addressType = new AddressType($name);
            $addressType->setLabel($label);
            $addressTypeEntities[] = $addressType;
        }

        $address = $this->createMock(AbstractDefaultTypedAddress::class);
        $address->expects($this->any())
            ->method('getDefaults')
            ->willReturn($addressTypeEntities);
        $address->expects($this->once())
            ->method('isEmpty')
            ->willReturn($isEmpty);

        return $address;
    }
}
