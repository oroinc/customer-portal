<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Extension\ScopeWithCustomerGroupAndCustomerExtension;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeCollectionType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

class ScopeWithCustomerGroupAndCustomerExtensionTest extends TestCase
{
    private ScopeWithCustomerGroupAndCustomerExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->extension = new ScopeWithCustomerGroupAndCustomerExtension();
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals(
            [ScopeCollectionType::class],
            ScopeWithCustomerGroupAndCustomerExtension::getExtendedTypes()
        );
    }

    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addEventListener');

        $this->extension->buildForm($builder, []);
    }
}
