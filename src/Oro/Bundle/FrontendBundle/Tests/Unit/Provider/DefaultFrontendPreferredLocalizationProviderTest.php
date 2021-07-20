<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\DefaultFrontendPreferredLocalizationProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\UserBundle\Entity\User;

class DefaultFrontendPreferredLocalizationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var UserLocalizationManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userLocalizationManager;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DefaultFrontendPreferredLocalizationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->userLocalizationManager = $this->createMock(UserLocalizationManager::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new DefaultFrontendPreferredLocalizationProvider(
            $this->userLocalizationManager,
            $this->frontendHelper
        );
    }

    /**
     * @dataProvider entityDataProvider
     *
     * @param mixed $entity
     */
    public function testSupportsForFrontendRequest($entity): void
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->assertTrue($this->provider->supports($entity));
    }

    /**
     * @dataProvider entityDataProvider
     *
     * @param mixed $entity
     */
    public function testSupportsForNotFrontendRequest($entity): void
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->assertFalse($this->provider->supports($entity));
    }

    public function testGetPreferredLocalizationWhenNotSupported(): void
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->expectException(\LogicException::class);
        $this->provider->getPreferredLocalization(new User());
    }

    /**
     * @dataProvider entityDataProvider
     *
     * @param mixed $entity
     */
    public function testGetPreferredLocalization($entity): void
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $localization = new Localization();
        $this->userLocalizationManager->expects($this->atLeastOnce())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->assertSame($localization, $this->provider->getPreferredLocalization($entity));
    }

    public function entityDataProvider(): array
    {
        return [
            [new User()],
            [new \stdClass()],
            [false],
            [null],
        ];
    }
}
