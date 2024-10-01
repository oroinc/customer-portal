<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Model;

use Oro\Bundle\FrontendBundle\Model\CssVariableConfig;
use PHPUnit\Framework\TestCase;

final class CssVariableConfigTest extends TestCase
{
    private CssVariableConfig $cssVariableConfig;

    #[\Override]
    protected function setUp(): void
    {
        $this->cssVariableConfig = new CssVariableConfig();
    }

    public function testSetValueAndGetValue(): void
    {
        $this->cssVariableConfig->setValue('100px');
        self::assertEquals('100px', $this->cssVariableConfig->getValue());

        $this->cssVariableConfig->setValue(null);
        self::assertNull($this->cssVariableConfig->getValue());
    }

    public function testSetVariableNameAndGetVariableName(): void
    {
        $this->cssVariableConfig->setVariableName('main-color');
        self::assertEquals('main-color', $this->cssVariableConfig->getVariableName());
    }
}
