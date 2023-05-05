<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CustomerGroupTypeTest extends FormIntegrationTestCase
{
    private CustomerGroupType $formType;

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
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    EntityIdentifierType::class => new EntityTypeStub()
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
