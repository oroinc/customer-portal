<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType as EntityTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Translation\TranslatorInterface;

class CustomerUserRoleSelectTypeTest extends FormIntegrationTestCase
{
    const ROLE_CLASS = 'Oro\Bundle\CustomerBundle\Entity\CustomerUserRole';

    /** @var  CustomerUserRoleSelectType */
    protected $formType;

    /** @var string */
    protected $roleClass;

    public function setUp()
    {
        $translator = $this->createTranslator();
        $this->formType = new CustomerUserRoleSelectType($translator);
        $this->formType->setRoleClass(self::ROLE_CLASS);
        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->formType);
        parent::tearDown();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $entityType = new EntityTypeStub([]);

        return [
            new PreloadedExtension(
                [
                    CustomerUserRoleSelectType::class => $this->formType,
                    EntityType::class => $entityType
                ],
                []
            )
        ];
    }

    public function testDefaultOptions()
    {
        $form = $this->factory->create(CustomerUserRoleSelectType::class);

        $expectedOptions = [
            'class' => self::ROLE_CLASS,
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ];

        $formOptions = $form->getConfig()->getOptions();

        $this->assertArraySubset($expectedOptions, $formOptions);
        $this->assertArrayHasKey('choice_label', $formOptions);
        $this->assertInternalType('callable', $formOptions['choice_label']);

        $roleWithoutCustomer = new CustomerUserRole();
        $roleWithoutCustomer->setLabel('roleWithoutCustomer');
        $this->assertEquals(
            'roleWithoutCustomer (oro.customer.customeruserrole.type.predefined.label.trans)',
            $formOptions['choice_label']($roleWithoutCustomer)
        );

        $customer = new Customer();
        $roleWithCustomer = new CustomerUserRole();
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

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    private function createTranslator()
    {
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(
                function ($message) {
                    return $message . '.trans';
                }
            );

        return $translator;
    }
}
