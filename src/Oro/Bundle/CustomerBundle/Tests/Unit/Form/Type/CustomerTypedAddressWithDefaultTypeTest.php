<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Repository\AddressTypeRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressWithDefaultType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomerTypedAddressWithDefaultTypeTest extends FormIntegrationTestCase
{
    /** @var CustomerTypedAddressWithDefaultType */
    protected $formType;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry */
    protected $registry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager */
    protected $em;

    /** @var AddressTypeRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $addressTypeRepository;

    protected function setUp(): void
    {
        $this->addressTypeRepository = $this->createRepositoryMock([
            new AddressType(AddressType::TYPE_BILLING),
            new AddressType(AddressType::TYPE_SHIPPING)
        ]);

        $this->em       = $this->createEntityManagerMock($this->addressTypeRepository);
        $this->registry = $this->createManagerRegistryMock($this->em);

        $translator = $this->createTranslatorMock();
        $this->formType = new CustomerTypedAddressWithDefaultType($translator);
        $this->formType->setRegistry($this->registry);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension([CustomerTypedAddressWithDefaultType::class => $this->formType], []),
        ];
    }

    /**
     * @dataProvider submitDataProvider
     *
     * @param array $options
     * @param mixed $defaultData
     * @param mixed $viewData
     * @param mixed $submittedData
     * @param mixed $expectedData
     */
    public function testSubmit(
        array $options,
        $defaultData,
        $viewData,
        $submittedData,
        $expectedData
    ) {
        $form = $this->factory->create(CustomerTypedAddressWithDefaultType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
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

    /**
     * @param array $entityModels
     * @return AddressTypeRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createRepositoryMock(array $entityModels = [])
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

    /**
     * @param $repo
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEntityManagerMock($repo)
    {
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->any())
            ->method('getRepository')
            ->willReturn($repo);
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($this->createClassMetadataMock());

        return $em;
    }

    /**
     * @param $em
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createManagerRegistryMock($em)
    {
        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($em);
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo('EntityManager'))
            ->willReturn($em);
        $registry->expects($this->any())
            ->method('getManager')
            ->willReturn($em);

        return $registry;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createClassMetadataMock()
    {
        $classMetadata = $this->createMock(ClassMetadataInfo::class);
        $classMetadata->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->willReturn('name');
        $classMetadata->expects($this->any())
            ->method('getReflectionProperty')
            ->willReturnCallback(function ($field) {
                return $this->createReflectionProperty($field);
            });

        return $classMetadata;
    }

    /**
     * @param $field
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function createReflectionProperty($field)
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

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    private function createTranslatorMock()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($message) {
                return $message . uniqid('trans', true);
            });

        return $translator;
    }
}
