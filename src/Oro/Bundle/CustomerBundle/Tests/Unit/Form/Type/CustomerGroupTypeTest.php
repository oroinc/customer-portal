<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CustomerGroupTypeTest extends FormIntegrationTestCase
{
    const DATA_CLASS = 'Oro\Bundle\CustomerBundle\Entity\CustomerGroup';
    const ACCOUNT_CLASS = 'Oro\Bundle\CustomerBundle\Entity\Customer';

    /**
     * @var CustomerGroupType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->formType = new CustomerGroupType();
        $this->formType->setDataClass(self::DATA_CLASS);
        $this->formType->setCustomerClass(self::ACCOUNT_CLASS);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->formType);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $entityIdentifierType = new EntityType([]);

        return [
            new PreloadedExtension(
                [
                    CustomerGroupType::class => $this->formType,
                    EntityIdentifierType::class => $entityIdentifierType
                ],
                []
            )
        ];
    }

    /**
     * @param array $options
     * @param mixed $defaultData
     * @param mixed $viewData
     * @param mixed $submittedData
     * @param mixed $expectedData
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        $defaultData,
        $viewData,
        $submittedData,
        $expectedData
    ) {
        $form = $this->factory->create(CustomerGroupType::class, $defaultData, $options);

        $this->assertTrue($form->has('appendCustomers'));
        $this->assertTrue($form->has('removeCustomers'));

        $formConfig = $form->getConfig();
        $this->assertEquals(self::DATA_CLASS, $formConfig->getOption('data_class'));

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
        $groupName = 'customer_group_name';
        $alteredGroupName = 'altered_group_name';

        $defaultGroup = new CustomerGroup();
        $defaultGroup->setName($groupName);

        /** @var CustomerGroup $existingGroupBefore */
        $existingGroupBefore = $this->getEntity(self::DATA_CLASS, 1);
        $existingGroupBefore->setName($groupName);

        $existingGroupAfter = clone $existingGroupBefore;
        $existingGroupAfter->setName($alteredGroupName);

        return [
            'empty' => [
                'options' => [],
                'defaultData' => null,
                'viewData' => null,
                'submittedData' => [
                    'name' => $groupName,
                ],
                'expectedData' => $defaultGroup
            ],
            'existing' => [
                'options' => [],
                'defaultData' => $existingGroupBefore,
                'viewData' => $existingGroupBefore,
                'submittedData' => [
                    'name' => $alteredGroupName,
                ],
                'expectedData' => $existingGroupAfter
            ]
        ];
    }

    /**
     * @param string $className
     * @param int $id
     * @return object
     */
    protected function getEntity($className, $id)
    {
        $entity = new $className;

        $reflectionClass = new \ReflectionClass($className);
        $method = $reflectionClass->getProperty('id');
        $method->setAccessible(true);
        $method->setValue($entity, $id);

        return $entity;
    }
}
