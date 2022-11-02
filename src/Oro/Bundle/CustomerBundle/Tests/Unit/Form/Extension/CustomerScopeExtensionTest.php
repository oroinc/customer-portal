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
    /** @var CustomerScopeExtension */
    protected $customerScopeExtension;

    /** @var ScopeManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $scopeManager;

    protected function setUp(): void
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

    public function testGetExtendedTypes()
    {
        $this->assertEquals([ScopeType::class], CustomerScopeExtension::getExtendedTypes());
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $this->scopeManager = $this->createMock(ScopeManager::class);

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
