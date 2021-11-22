<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextType;
use Oro\Bundle\PayPalBundle\PayPal\Payflow\Option\OptionsResolver;
use Oro\Bundle\PricingBundle\Form\Type\PriceRuleEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RuleEditorTextTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var RuleEditorOptionsConfigurator|\PHPUnit\Framework\MockObject\MockObject */
    private $optionsConfigurator;

    /** @var PriceRuleEditorType */
    private $type;

    protected function setUp(): void
    {
        $this->optionsConfigurator = $this->createMock(RuleEditorOptionsConfigurator::class);

        $this->type = new RuleEditorTextType($this->optionsConfigurator);
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals(RuleEditorTextType::NAME, $this->type->getBlockPrefix());
    }

    public function testGetParent()
    {
        $this->assertEquals(TextType::class, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = new OptionsResolver();

        $this->optionsConfigurator->expects($this->once())
            ->method('configureOptions')
            ->with($resolver);

        $this->type->configureOptions($resolver);
    }
}
