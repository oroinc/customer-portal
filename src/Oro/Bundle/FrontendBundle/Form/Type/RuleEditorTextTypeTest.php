<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Oro\Bundle\PayPalBundle\PayPal\Payflow\Option\OptionsResolver;
use Oro\Bundle\PricingBundle\Form\Type\PriceRuleEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RuleEditorTextTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuleEditorOptionsConfigurator|\PHPUnit_Framework_MockObject_MockObject
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
