<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Extension\CustomerGroupScopeExtension;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension\Stub\CustomerGroupSelectTypeStub;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeType;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CustomerGroupScopeExtensionTest extends FormIntegrationTestCase
{
    /** @var CustomerGroupScopeExtension */
    protected $customerGroupScopeExtension;

    /** @var ScopeManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $scopeManager;

    protected function setUp(): void
    {
        $this->customerGroupScopeExtension = new CustomerGroupScopeExtension();

        parent::setUp();
    }

    public function testBuildForm()
    {
        $this->scopeManager->expects($this->once())
            ->method('getScopeEntities')
            ->with('web_content')
            ->willReturn(['customerGroup' => CustomerGroup::class]);

        $form = $this->factory->create(
            ScopeType::class,
            null,
            [ScopeType::SCOPE_TYPE_OPTION => 'web_content']
        );

        $this->assertTrue($form->has('customerGroup'));
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([ScopeType::class], CustomerGroupScopeExtension::getExtendedTypes());
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
                    CustomerGroupSelectType::class => new CustomerGroupSelectTypeStub(),
                ],
                [
                    ScopeType::class => [$this->customerGroupScopeExtension],
                ]
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
