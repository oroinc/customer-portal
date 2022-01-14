<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;

class CustomerUserRoleTypeTest extends AbstractCustomerUserRoleTypeTest
{
    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        ?CustomerUserRole $defaultData,
        ?CustomerUserRole $viewData,
        array $submittedData,
        ?CustomerUserRole $expectedData
    ) {
        $form = $this->factory->create(CustomerUserRoleType::class, $defaultData, $options);

        $this->assertTrue($form->has('appendUsers'));
        $this->assertTrue($form->has('removeUsers'));
        $this->assertTrue($form->has('customer'));
        $this->assertTrue($form->has('selfManaged'));

        $formConfig = $form->getConfig();
        $this->assertEquals(CustomerUserRole::class, $formConfig->getOption('data_class'));

        $this->assertFalse($formConfig->getOption('hide_self_managed'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $actualData = $form->getData();

        $actualDataWithDummyRole = clone $actualData;
        $actualDataWithDummyRole->setRole('', false);
        $expectedDataWithDummyRole = clone $expectedData;
        $expectedDataWithDummyRole->setRole('', false);
        $this->assertEquals($actualDataWithDummyRole, $expectedDataWithDummyRole);

        if ($defaultData && $defaultData->getRole()) {
            $this->assertEquals($expectedData->getRole(), $actualData->getRole());
        } else {
            $this->assertNotEmpty($actualData->getRole());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function createCustomerUserRoleFormTypeAndSetDataClass(): void
    {
        $this->formType = new CustomerUserRoleType();
        $this->formType->setDataClass(CustomerUserRole::class);
    }
}
