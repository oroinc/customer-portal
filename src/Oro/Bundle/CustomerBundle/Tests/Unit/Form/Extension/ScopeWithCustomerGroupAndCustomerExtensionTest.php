<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Extension\ScopeWithCustomerGroupAndCustomerExtension;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeCollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class ScopeWithCustomerGroupAndCustomerExtensionTest extends \PHPUnit\Framework\TestCase
{
    private ScopeWithCustomerGroupAndCustomerExtension $extension;

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
        $builder
            ->expects($this->once())
            ->method('addEventListener');

        $this->extension->buildForm($builder, []);
    }
}
