<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextareaType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorTextareaTypeTest extends TestCase
{
    private RuleEditorOptionsConfigurator&MockObject $optionsConfigurator;
    private RuleEditorTextareaType $type;

    #[\Override]
    protected function setUp(): void
    {
        $this->optionsConfigurator = $this->createMock(RuleEditorOptionsConfigurator::class);

        $this->type = new RuleEditorTextareaType($this->optionsConfigurator);
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertEquals(RuleEditorTextareaType::NAME, $this->type->getBlockPrefix());
    }

    public function testGetParent(): void
    {
        $this->assertEquals(TextareaType::class, $this->type->getParent());
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->optionsConfigurator->expects($this->once())
            ->method('configureOptions')
            ->with($this->identicalTo($resolver));

        $this->type->configureOptions($resolver);
    }
}
