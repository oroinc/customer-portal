<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Form\Extension\CustomerScopeExtension;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension\Stub\CustomerSelectTypeStub;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeType;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CustomerScopeExtensionTest extends FormIntegrationTestCase
{
    /**
     * @var CustomerScopeExtension
     */
    protected $customerScopeExtension;

    /**
     * @var ScopeManager|\PHPUnit\Framework\MockObject\MockObject $scopeManager
     */
    protected $scopeManager;

    protected function setUp()
    {
        $this->customerScopeExtension = new CustomerScopeExtension();

        parent::setUp();
    }

    public function testBuildForm()
    {
        $this->scopeManager->expects($this->once())
            ->method('getScopeEntities')
            ->with('web_content')
            ->willReturn(['customer' => Customer::class]);

        $form = $this->factory->create(
            ScopeType::class,
            null,
            [ScopeType::SCOPE_TYPE_OPTION => 'web_content']
        );

        $this->assertTrue($form->has('customer'));
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(ScopeType::class, $this->customerScopeExtension->getExtendedType());
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $this->scopeManager = $this->getMockBuilder(ScopeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new PreloadedExtension(
                [
                    ScopeType::class => new ScopeType($this->scopeManager),
                    CustomerSelectType::class => new CustomerSelectTypeStub(),
                ],
                [
                    ScopeType::class => [$this->customerScopeExtension],
                ]
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
