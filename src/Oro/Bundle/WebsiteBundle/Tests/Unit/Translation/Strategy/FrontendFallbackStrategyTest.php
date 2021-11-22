<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Translation\Strategy;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\TranslationBundle\Strategy\DefaultTranslationStrategy;
use Oro\Bundle\WebsiteBundle\Translation\Strategy\FrontendFallbackStrategy;

class FrontendFallbackStrategyTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DefaultTranslationStrategy|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendStrategy;

    /** @var FrontendFallbackStrategy */
    private $strategy;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->frontendStrategy = $this->createMock(DefaultTranslationStrategy::class);

        $this->strategy = new FrontendFallbackStrategy($this->frontendHelper, $this->frontendStrategy);
    }

    public function testIsApplicable()
    {
        $isApplicable = true;

        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isApplicable);

        $this->assertSame($isApplicable, $this->strategy->isApplicable());
    }

    public function testGetLocaleFallbacks()
    {
        $locales = [
            'en' => ['en_EN' => ['en_FR' => []]],
            'ru' => ['ru_RU' => []],
        ];

        $this->frontendStrategy->expects($this->once())
            ->method('getLocaleFallbacks')
            ->willReturn($locales);

        $this->assertSame($locales, $this->strategy->getLocaleFallbacks());
    }

    public function testGetName()
    {
        $name = 'strategy_name';
        $this->frontendStrategy->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $this->assertSame($name, $this->strategy->getName());
    }
}
