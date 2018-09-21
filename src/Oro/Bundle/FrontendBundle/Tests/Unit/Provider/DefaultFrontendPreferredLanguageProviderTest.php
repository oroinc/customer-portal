<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\DefaultFrontendPreferredLanguageProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\TranslationBundle\Entity\Language;

class DefaultFrontendPreferredLanguageProviderTest extends \PHPUnit_Framework_TestCase
{
    private const LANGUAGE = 'fr_FR';

    /**
     * @var UserLocalizationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userLocalizationManager;

    /**
     * @var FrontendHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendHelper;

    /**
     * @var DefaultFrontendPreferredLanguageProvider
     */
    private $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->userLocalizationManager = $this->createMock(UserLocalizationManager::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new DefaultFrontendPreferredLanguageProvider(
            $this->userLocalizationManager,
            $this->frontendHelper
        );
    }

    /**
     * @dataProvider supportsDataProvider
     * @param bool $isFrontend
     */
    public function testSupports(bool $isFrontend): void
    {
        $this->frontendHelper
            ->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn($isFrontend);

        self::assertEquals($isFrontend, $this->provider->supports(new \stdClass()));
    }

    /**
     * @return array
     */
    public function supportsDataProvider(): array
    {
        return [
            'frontend request' => [true],
            'backend request' => [false]
        ];
    }

    public function testGetPreferredLanguageWhenNotSupports(): void
    {
        $this->frontendHelper
            ->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->expectException(\LogicException::class);

        $this->provider->getPreferredLanguage(new \stdClass());
    }

    public function testGetPreferredLanguageWhenDefaultLocalizationExists(): void
    {
        $localization = (new Localization())->setLanguage((new Language())->setCode(self::LANGUAGE));
        $this->frontendHelper
            ->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->userLocalizationManager
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        self::assertEquals(self::LANGUAGE, $this->provider->getPreferredLanguage(new \stdClass()));
    }

    public function testGetPreferredLanguageWhenNoDefaultLocalizationExists(): void
    {
        $this->frontendHelper
            ->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->userLocalizationManager
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        self::assertEquals(Configuration::DEFAULT_LANGUAGE, $this->provider->getPreferredLanguage(new \stdClass()));
    }
}
