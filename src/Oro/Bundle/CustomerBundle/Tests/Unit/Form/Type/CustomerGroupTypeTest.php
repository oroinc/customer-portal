<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CustomerGroupTypeTest extends FormIntegrationTestCase
{
    /** @var CustomerGroupType */
    private $formType;

    protected function setUp(): void
    {
        $this->formType = new CustomerGroupType();
        $this->formType->setDataClass(CustomerGroup::class);
        $this->formType->setCustomerClass(Customer::class);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
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
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        mixed $defaultData,
        mixed $viewData,
        mixed $submittedData,
        mixed $expectedData
    ) {
        $form = $this->factory->create(CustomerGroupType::class, $defaultData, $options);

        $this->assertTrue($form->has('appendCustomers'));
        $this->assertTrue($form->has('removeCustomers'));

        $formConfig = $form->getConfig();
        $this->assertEquals(CustomerGroup::class, $formConfig->getOption('data_class'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitDataProvider(): array
    {
        $groupName = 'customer_group_name';
        $alteredGroupName = 'altered_group_name';

        $defaultGroup = new CustomerGroup();
        $defaultGroup->setName($groupName);

        $existingGroupBefore = new CustomerGroup();
        ReflectionUtil::setId($existingGroupBefore, 1);
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
}
