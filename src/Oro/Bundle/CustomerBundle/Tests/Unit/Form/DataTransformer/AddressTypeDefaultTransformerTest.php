<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Form\DataTransformer\AddressTypeDefaultTransformer;

class AddressTypeDefaultTransformerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddressTypeDefaultTransformer */
    private $transformer;

    /** @var AddressType */
    private $billingAddressType;

    /** @var AddressType */
    private $shippingAddressType;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->billingAddressType = new AddressType(AddressType::TYPE_BILLING);
        $this->shippingAddressType = new AddressType(AddressType::TYPE_SHIPPING);
    }

    protected function setUp(): void
    {
        $addressRepository = $this->createMock(EntityRepository::class);
        $addressRepository->expects($this->any())
            ->method('findAll')
            ->willReturn([$this->billingAddressType, $this->shippingAddressType]);
        $addressRepository->expects($this->any())
            ->method('findBy')
            ->willReturnCallback(function ($params) {
                $result = [];
                foreach ($params['name'] as $name) {
                    switch ($name) {
                        case AddressType::TYPE_BILLING:
                            $result[] = $this->billingAddressType;
                            break;
                        case AddressType::TYPE_SHIPPING:
                            $result[] = $this->shippingAddressType;
                            break;
                    }
                }

                return $result;
            });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
            ->method('getRepository')
            ->willReturn($addressRepository);

        $this->transformer = new AddressTypeDefaultTransformer($em);
    }

    /**
     * @dataProvider transformerProvider
     */
    public function testTransform(?array $parameters, array $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($parameters));
    }

    public function transformerProvider(): array
    {
        return [
            'nullable params' => [
                'parameters' => null,
                'expected' => []
            ],
            'default' => [
                'parameters' => [$this->shippingAddressType, $this->billingAddressType],
                'expected' => ['default' => [AddressType::TYPE_SHIPPING, AddressType::TYPE_BILLING]]
            ],
        ];
    }

    /**
     * @dataProvider reverseTransformerProvider
     */
    public function testReverseTransform(array $parameters, array $expected)
    {
        $this->assertEquals($expected, $this->transformer->reverseTransform($parameters));
    }

    public function reverseTransformerProvider(): array
    {
        return [
            'nullable params' => [
                'parameters' => ['default' => null],
                'expected' => []
            ],
            'empty params' => [
                'parameters' => [],
                'expected' => []
            ],
            'default' => [
                'parameters' => ['default' => [AddressType::TYPE_SHIPPING, AddressType::TYPE_BILLING]],
                'expected' => [$this->shippingAddressType, $this->billingAddressType]
            ],
        ];
    }
}
