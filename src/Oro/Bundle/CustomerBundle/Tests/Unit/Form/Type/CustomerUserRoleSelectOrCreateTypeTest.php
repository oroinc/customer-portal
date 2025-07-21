<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectOrCreateType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserRoleSelectOrCreateTypeTest extends TestCase
{
    protected CustomerUserRoleSelectOrCreateType $type;

    #[\Override]
    protected function setUp(): void
    {
        $this->type = new CustomerUserRoleSelectOrCreateType();
    }

    public function testGetParent(): void
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(function (array $options) use ($resolver) {
                $this->assertArrayHasKey('autocomplete_alias', $options);
                $this->assertArrayHasKey('create_form_route', $options);
                $this->assertArrayHasKey('grid_name', $options);

                return $resolver;
            });

        $this->type->configureOptions($resolver);
    }
}
