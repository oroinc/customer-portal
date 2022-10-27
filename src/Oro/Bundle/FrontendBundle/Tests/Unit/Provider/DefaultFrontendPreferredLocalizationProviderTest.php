<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\DefaultFrontendPreferredLocalizationProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\LocalizationProviderInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultFrontendPreferredLocalizationProviderTest extends TestCase
{
    private LocalizationProviderInterface|MockObject $localizationProvider;

    private FrontendHelper|MockObject $frontendHelper;

    private DefaultFrontendPreferredLocalizationProvider $provider;

    protected function setUp(): void
    {
        $this->localizationProvider = $this->createMock(LocalizationProviderInterface::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new DefaultFrontendPreferredLocalizationProvider(
            $this->localizationProvider,
            $this->frontendHelper
        );
    }

    /**
     * @dataProvider entityDataProvider
     */
    public function testSupportsForFrontendRequest(mixed $entity): void
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->assertTrue($this->provider->supports($entity));
    }

    /**
     * @dataProvider entityDataProvider
     */
    public function testSupportsForNotFrontendRequest(mixed $entity): void
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
     */
    public function testGetPreferredLocalization(mixed $entity): void
    {
        $this->frontendHelper->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $localization = new Localization();
        $this->localizationProvider->expects($this->atLeastOnce())
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
