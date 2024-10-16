<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectOrCreateType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserRoleSelectOrCreateTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserRoleSelectOrCreateType */
    protected $type;

    #[\Override]
    protected function setUp(): void
    {
        $this->type = new CustomerUserRoleSelectOrCreateType();
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) use ($resolver) {
                    $this->assertArrayHasKey('autocomplete_alias', $options);
                    $this->assertArrayHasKey('create_form_route', $options);
                    $this->assertArrayHasKey('grid_name', $options);

                    return $resolver;
                }
            );

        $this->type->configureOptions($resolver);
    }
}
