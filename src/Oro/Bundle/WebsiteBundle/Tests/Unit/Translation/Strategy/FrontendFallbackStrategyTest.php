<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Translation\Strategy;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\TranslationBundle\Strategy\DefaultTranslationStrategy;
use Oro\Bundle\WebsiteBundle\Translation\Strategy\FrontendFallbackStrategy;

class FrontendFallbackStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendHelper;

    /**
     * @var DefaultTranslationStrategy|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendStrategy;

    /**
     * @var FrontendFallbackStrategy
     */
    protected $strategy;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->getMockBuilder(FrontendHelper::class)
            ->disableOriginalConstructor()->getMock();

        $this->frontendStrategy = $this->getMockBuilder(DefaultTranslationStrategy::class)
            ->disableOriginalConstructor()->getMock();

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
