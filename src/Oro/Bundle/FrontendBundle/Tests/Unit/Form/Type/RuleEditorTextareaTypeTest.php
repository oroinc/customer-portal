<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorTextareaTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var RuleEditorOptionsConfigurator|\PHPUnit\Framework\MockObject\MockObject */
    private $optionsConfigurator;

    /** @var RuleEditorTextareaType */
    private $type;

    protected function setUp(): void
    {
        $this->optionsConfigurator = $this->createMock(RuleEditorOptionsConfigurator::class);

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
            ->with($this->identicalTo($resolver));

        $this->type->configureOptions($resolver);
    }
}
