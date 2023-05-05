<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserRoleSelectTypeTest extends FormIntegrationTestCase
{
    private CustomerUserRoleSelectType $formType;

    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($message) {
                return $message . '.trans';
            });

        $this->formType = new CustomerUserRoleSelectType($translator);
        $this->formType->setRoleClass(CustomerUserRole::class);
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
                    EntityType::class => new EntityTypeStub()
                ],
                []
            )
        ];
    }

    public function testDefaultOptions()
    {
        $form = $this->factory->create(CustomerUserRoleSelectType::class);

        $formOptions = $form->getConfig()->getOptions();
        $this->assertSame(CustomerUserRole::class, $formOptions['class']);
        $this->assertTrue($formOptions['multiple']);
        $this->assertTrue($formOptions['expanded']);
        $this->assertFalse($formOptions['required']);

        $this->assertArrayHasKey('choice_label', $formOptions);
        $this->assertIsCallable($formOptions['choice_label']);

        $roleWithoutCustomer = new CustomerUserRole('');
        $roleWithoutCustomer->setLabel('roleWithoutCustomer');
        $this->assertEquals(
            'roleWithoutCustomer (oro.customer.customeruserrole.type.predefined.label.trans)',
            $formOptions['choice_label']($roleWithoutCustomer)
        );

        $customer = new Customer();
        $roleWithCustomer = new CustomerUserRole('');
        $roleWithCustomer->setCustomer($customer);
        $roleWithCustomer->setLabel('roleWithCustomer');
        $this->assertEquals(
            'roleWithCustomer (oro.customer.customeruserrole.type.customizable.label.trans)',
            $formOptions['choice_label']($roleWithCustomer)
        );

        $testEntity = new Customer();
        $testEntity->setName('TestEntityValue');
        $this->assertEquals('TestEntityValue', $formOptions['choice_label']($testEntity));
    }
}
