<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Oro\Bundle\PayPalBundle\PayPal\Payflow\Option\OptionsResolver;
use Oro\Bundle\PricingBundle\Form\Type\PriceRuleEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RuleEditorTextareaTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RuleEditorOptionsConfigurator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $optionsConfigurator;

    /**
     * @var PriceRuleEditorType
     */
    private $type;

    protected function setUp()
    {
        $this->optionsConfigurator = $this->getMockBuilder(RuleEditorOptionsConfigurator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new RuleEditorTextareaType($this->optionsConfigurator);
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals(RuleEditorTextareaType::NAME, $this->type->getBlockPrefix());
    }

    public function testGetParent()
    {
        $this->assertEquals(TextareaType::class, $this->type->getParent());
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
