<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Repository\AddressTypeRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressWithDefaultType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomerTypedAddressWithDefaultTypeTest extends FormIntegrationTestCase
{
    private CustomerTypedAddressWithDefaultType $formType;

    protected function setUp(): void
    {
        $addressTypeRepository = $this->getAddressTypeRepository([
            new AddressType(AddressType::TYPE_BILLING),
            new AddressType(AddressType::TYPE_SHIPPING)
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
            ->method('getRepository')
            ->willReturn($addressTypeRepository);
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($this->getClassMetadata());

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($em);
        $doctrine->expects($this->any())
            ->method('getManager')
            ->with('EntityManager')
            ->willReturn($em);
        $doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($em);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($message) {
                return $message . uniqid('trans', true);
            });

        $this->formType = new CustomerTypedAddressWithDefaultType($translator);
        $this->formType->setRegistry($doctrine);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([$this->formType], []),
        ];
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        mixed $defaultData,
        mixed $viewData,
        mixed $submittedData,
        mixed $expectedData
    ) {
        $form = $this->factory->create(CustomerTypedAddressWithDefaultType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitDataProvider(): array
    {
        return [
            'without defaults' => [
                'options'       => ['class' => AddressType::class],
                'defaultData'   => [],
                'viewData'      => [],
                'submittedData' => [
                    'default' => [],
                ],
                'expectedData'  => [],
            ],
            'all default types' => [
                'options'       => ['class' => AddressType::class],
                'defaultData'   => [],
                'viewData'      => [],
                'submittedData' => [
                    'default' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
                ],
                'expectedData'  => [
                    new AddressType(AddressType::TYPE_BILLING),
                    new AddressType(AddressType::TYPE_SHIPPING)
                ],
            ],
            'one default type' => [
                'options'       => ['class' => AddressType::class],
                'defaultData'   => [],
                'viewData'      => [],
                'submittedData' => [
                    'default' => [AddressType::TYPE_SHIPPING],
                ],
                'expectedData'  => [new AddressType(AddressType::TYPE_SHIPPING)],
            ],
            'all default types with custom em' => [
                'options'       => ['class' => AddressType::class, 'em' => 'EntityManager'],
                'defaultData'   => [],
                'viewData'      => [],
                'submittedData' => [
                    'default' => [AddressType::TYPE_SHIPPING],
                ],
                'expectedData'  => [new AddressType(AddressType::TYPE_SHIPPING)],
            ],
            'all default types with custom property' => [
                'options'       => ['class' => AddressType::class, 'property' => 'name'],
                'defaultData'   => [],
                'viewData'      => [],
                'submittedData' => [
                    'default' => [AddressType::TYPE_SHIPPING],
                ],
                'expectedData'  => [new AddressType(AddressType::TYPE_SHIPPING)],
            ],
        ];
    }

    private function getAddressTypeRepository(array $entityModels = []): AddressTypeRepository
    {
        $repo = $this->createMock(AddressTypeRepository::class);
        $repo->expects($this->any())
            ->method('getBatchIterator')
            ->willReturn($entityModels);

        $repo->expects($this->any())
            ->method('findBy')
            ->willReturnCallback(function ($params) {
                $result = [];
                foreach ($params['name'] as $name) {
                    switch ($name) {
                        case AddressType::TYPE_BILLING:
                            $result[] = new AddressType(AddressType::TYPE_BILLING);
                            break;
                        case AddressType::TYPE_SHIPPING:
                            $result[] = new AddressType(AddressType::TYPE_SHIPPING);
                            break;
                    }
                }

                return $result;
            });

        return $repo;
    }

    private function getClassMetadata(): ClassMetadataInfo
    {
        $classMetadata = $this->createMock(ClassMetadataInfo::class);
        $classMetadata->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->willReturn('name');
        $classMetadata->expects($this->any())
            ->method('getReflectionProperty')
            ->willReturnCallback(function ($field) {
                return $this->getReflectionProperty($field);
            });

        return $classMetadata;
    }

    private function getReflectionProperty(string $field): \ReflectionProperty
    {
        $class = $this->createMock(\ReflectionProperty::class);
        $class->expects($this->any())
            ->method('getValue')
            ->willReturnCallback(function ($entity) use ($field) {
                $method = 'get' . ucfirst($field);

                return $entity->$method();
            });

        return $class;
    }
}
